#include <Arduino.h>

#include <WiFi.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include <DHT.h>

// =====================================================
// PIN SETTINGS
// =====================================================
#define SOIL_PIN 34
#define RAIN_PIN 35
#define RELAY_PIN 26

#define DHT_PIN 4
#define DHT_TYPE DHT22

DHT dht(DHT_PIN, DHT_TYPE);
WebServer server(80);

// =====================================================
// WIFI ACCESS POINT SETTINGS
// =====================================================
const char *AP_SSID = "Watering-System";
const char *AP_PASSWORD = "12345678"; // Minimum 8 characters

// =====================================================
// LARAVEL API SETTINGS
// Update these before flashing the ESP32.
// BACKEND_SENSOR_URL example: http://192.168.1.10:8000/api/sensor-readings
// DEVICE_API_KEY must match the api_key column in the Laravel devices table.
// =====================================================
const char *STA_SSID = "syaiwan13";
const char *STA_PASSWORD = "123456789";
// const char *BACKEND_SENSOR_URL = "http://192.168.1.8:8000/api/sensor-readings";
const char *BACKEND_SENSOR_URL = "https://smartsprayer.web.id/api/sensor-readings";
const char *DEVICE_API_KEY = "HtfEoED9PhayKSg46lydZxa2QAkUfTas";

bool backendControlMode = true;
const unsigned long BACKEND_SYNC_INTERVAL_MS = 2000;

// =====================================================
// CONFIGURABLE PARAMETERS
// These can be changed from the web page.
// =====================================================
bool simulationMode = false;
bool relaySimulation = false; // true = real relay follows pump state during simulation
bool relayActiveLow = true;
bool rainWetBelowThreshold = true;

// HW-103 soil moisture sensor calibration
// Usually dry soil gives a higher raw value.
// Wet soil usually gives a lower raw value.
int soilDryRaw = 3000;
int soilWetRaw = 1300;
int rainWetThreshold = 2200;

int waterWhenBelow = 55;
int stopWhenAbove = 80;

unsigned long maxPumpTimeMs = 20000;
unsigned long cooldownMs = 15000;

float simDryRate = 1.2;
float simRainWetRate = 1.2;
float simPumpWetRate = 2.0;
unsigned long simRainToggleMs = 45000;

// =====================================================
// SYSTEM STATE
// =====================================================
bool pumpOn = false;
unsigned long pumpStartedAt = 0;
unsigned long lastWateredAt = 0;

// =====================================================
// SENSOR STATE
// =====================================================
int currentSoilRaw = 0;
int currentSoilPercent = 0;
int currentRainRaw = 0;
bool currentRaining = false;
float currentTemperature = 0;
float currentHumidity = 0;

// =====================================================
// SIMULATION VARIABLES
// =====================================================
float simSoilPercent = 60.0;
float simTemperature = 29.0;
float simHumidity = 70.0;
bool simRaining = false;

unsigned long lastSimulationUpdate = 0;
unsigned long lastRainToggle = 0;
unsigned long lastLoopRun = 0;
unsigned long lastBackendSync = 0;

// =====================================================
// WEB SERIAL LOG BUFFER
// =====================================================
const int LOG_LINES = 80;
String logBuffer[LOG_LINES];
int logIndex = 0;
int logCount = 0;

void addLog(String message)
{
  String line = String(millis() / 1000) + "s | " + message;
  Serial.println(line);

  logBuffer[logIndex] = line;
  logIndex = (logIndex + 1) % LOG_LINES;
  if (logCount < LOG_LINES)
  {
    logCount++;
  }
}

String getLogsText()
{
  String output = "";

  int start = (logIndex - logCount + LOG_LINES) % LOG_LINES;
  for (int i = 0; i < logCount; i++)
  {
    int idx = (start + i) % LOG_LINES;
    output += logBuffer[idx] + "\n";
  }

  return output;
}

// =====================================================
// RELAY CONTROL
// =====================================================
bool shouldControlRelayHardware()
{
  return !simulationMode || relaySimulation;
}

void writeRelayHardware(bool turnOn)
{
  if (turnOn)
  {
    digitalWrite(RELAY_PIN, relayActiveLow ? LOW : HIGH);
  }
  else
  {
    digitalWrite(RELAY_PIN, relayActiveLow ? HIGH : LOW);
  }
}

void relayOff()
{
  writeRelayHardware(false);

  pumpOn = false;
  addLog("GPIO command: RELAY OFF / Pump OFF");
}

void relayOn()
{
  if (shouldControlRelayHardware())
  {
    writeRelayHardware(true);
    addLog("GPIO command: RELAY ON / Pump ON");
  }
  else
  {
    writeRelayHardware(false);
    addLog("SIMULATION: Pump ON in software only. Real relay remains OFF.");
  }

  pumpOn = true;
  pumpStartedAt = millis();
}

// =====================================================
// SIMULATION ENGINE
// =====================================================
void updateSimulation()
{
  unsigned long now = millis();

  if (now - lastSimulationUpdate < 1000)
  {
    return;
  }

  lastSimulationUpdate = now;

  if (!pumpOn && !simRaining)
  {
    simSoilPercent -= simDryRate;
  }

  if (simRaining)
  {
    simSoilPercent += simRainWetRate;
  }

  if (pumpOn)
  {
    simSoilPercent += simPumpWetRate;
  }

  simSoilPercent = constrain(simSoilPercent, 0, 100);

  simTemperature += random(-2, 3) * 0.1;
  simHumidity += random(-3, 4) * 0.2;
  simHumidity = constrain(simHumidity, 40, 95);

  if (now - lastRainToggle > simRainToggleMs)
  {
    simRaining = !simRaining;
    lastRainToggle = now;
    addLog(String("SIMULATION: Rain changed to ") + (simRaining ? "YES" : "NO"));
  }
}

// =====================================================
// SENSOR READ FUNCTIONS
// =====================================================
int readSoilRaw()
{
  if (simulationMode)
  {
    return map((int)simSoilPercent, 0, 100, soilDryRaw, soilWetRaw);
  }

  return analogRead(SOIL_PIN);
}

int readSoilPercentFromRaw(int raw)
{
  int percent = map(raw, soilDryRaw, soilWetRaw, 0, 100);
  return constrain(percent, 0, 100);
}

int readRainRaw()
{
  if (simulationMode)
  {
    return simRaining ? 1200 : 3500;
  }

  return analogRead(RAIN_PIN);
}

bool readRainingFromRaw(int raw)
{
  if (simulationMode)
  {
    return simRaining;
  }

  if (rainWetBelowThreshold)
  {
    return raw < rainWetThreshold;
  }

  return raw > rainWetThreshold;
}

float readTemperature()
{
  if (simulationMode)
  {
    return simTemperature;
  }

  float temperature = dht.readTemperature();
  if (isnan(temperature))
  {
    addLog("DHT22 temperature read failed.");
    return -999;
  }

  return temperature;
}

float readHumidity()
{
  if (simulationMode)
  {
    return simHumidity;
  }

  float humidity = dht.readHumidity();
  if (isnan(humidity))
  {
    addLog("DHT22 humidity read failed.");
    return -999;
  }

  return humidity;
}

void readAllSensors()
{
  currentSoilRaw = readSoilRaw();
  currentSoilPercent = readSoilPercentFromRaw(currentSoilRaw);

  currentRainRaw = readRainRaw();
  currentRaining = readRainingFromRaw(currentRainRaw);

  currentTemperature = readTemperature();
  currentHumidity = readHumidity();
}

// =====================================================
// LARAVEL API SYNC
// =====================================================
bool isBackendConfigured()
{
  return String(STA_SSID) != "ISI_NAMA_WIFI" &&
         String(BACKEND_SENSOR_URL).startsWith("http") &&
         String(DEVICE_API_KEY) != "ISI_API_KEY_DEVICE";
}

void connectToWiFiStation()
{
  if (!isBackendConfigured())
  {
    addLog("Laravel API sync is not configured. Update STA_SSID, BACKEND_SENSOR_URL, and DEVICE_API_KEY.");
    return;
  }

  addLog(String("Connecting to WiFi STA: ") + STA_SSID);
  WiFi.begin(STA_SSID, STA_PASSWORD);

  unsigned long startedAt = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startedAt < 10000)
  {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() == WL_CONNECTED)
  {
    addLog(String("WiFi STA connected. IP: ") + WiFi.localIP().toString());
  }
  else
  {
    addLog("WiFi STA connection failed. Local fallback logic will be used when sync fails.");
  }
}

String extractJsonString(String json, String key)
{
  String marker = "\"" + key + "\"";
  int keyIndex = json.indexOf(marker);
  if (keyIndex < 0)
  {
    return "";
  }

  int colonIndex = json.indexOf(":", keyIndex + marker.length());
  int quoteStart = json.indexOf("\"", colonIndex + 1);
  int quoteEnd = json.indexOf("\"", quoteStart + 1);

  if (colonIndex < 0 || quoteStart < 0 || quoteEnd < 0)
  {
    return "";
  }

  return json.substring(quoteStart + 1, quoteEnd);
}

String buildBackendPayload()
{
  String payload = "{";
  payload += "\"temperature\":" + String(currentTemperature, 1) + ",";
  payload += "\"humidity\":" + String(currentHumidity, 1) + ",";
  payload += "\"soilPercent\":" + String(currentSoilPercent) + ",";
  payload += "\"raining\":" + String(currentRaining ? "true" : "false") + ",";
  payload += "\"pumpOn\":" + String(pumpOn ? "true" : "false") + ",";
  payload += "\"soilRaw\":" + String(currentSoilRaw) + ",";
  payload += "\"rainRaw\":" + String(currentRainRaw) + ",";
  payload += "\"simulationMode\":" + String(simulationMode ? "true" : "false");
  payload += "}";

  return payload;
}

void applyBackendCommand(String command)
{
  if (command == "on" && !pumpOn)
  {
    addLog("Laravel command: Pump ON.");
    relayOn();
    return;
  }

  if (command == "off" && pumpOn)
  {
    addLog("Laravel command: Pump OFF.");
    relayOff();
    lastWateredAt = millis();
    return;
  }

  if (command == "on" || command == "off")
  {
    addLog(String("Laravel command keeps pump ") + command + ".");
  }
}

bool syncSensorDataWithBackend()
{
  if (!isBackendConfigured())
  {
    return false;
  }

  if (WiFi.status() != WL_CONNECTED)
  {
    addLog("Laravel sync skipped: WiFi STA disconnected. Reconnecting...");
    WiFi.reconnect();
    return false;
  }

  HTTPClient http;
  http.begin(BACKEND_SENSOR_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Api-Key", DEVICE_API_KEY);

  String payload = buildBackendPayload();
  int statusCode = http.POST(payload);
  String response = http.getString();
  http.end();

  addLog("Laravel sync HTTP " + String(statusCode) + ": " + response);

  if (statusCode < 200 || statusCode >= 300)
  {
    return false;
  }

  String command = extractJsonString(response, "sprayer_command");
  applyBackendCommand(command);

  return command == "on" || command == "off";
}

// =====================================================
// WATERING DECISION LOGIC
// =====================================================
void runWateringLogic()
{
  unsigned long now = millis();
  bool cooldownDone = (now - lastWateredAt) > cooldownMs;

  if (!pumpOn)
  {
    if (currentSoilPercent < waterWhenBelow && !currentRaining && cooldownDone)
    {
      addLog("Decision: Soil dry and no rain. Pump ON.");
      relayOn();
    }
    else
    {
      String reason = "Decision: Pump remains OFF. ";

      if (currentSoilPercent >= waterWhenBelow)
      {
        reason += "Soil is moist enough. ";
      }

      if (currentRaining)
      {
        reason += "Rain detected. ";
      }

      if (!cooldownDone)
      {
        reason += "Cooldown active. ";
      }

      addLog(reason);
    }
  }
  else
  {
    bool maxTimeReached = (now - pumpStartedAt) > maxPumpTimeMs;
    bool soilWetEnough = currentSoilPercent >= stopWhenAbove;

    if (maxTimeReached || soilWetEnough || currentRaining)
    {
      String reason = "Decision: Pump OFF. ";

      if (maxTimeReached)
      {
        reason += "Maximum pump time reached. ";
      }

      if (soilWetEnough)
      {
        reason += "Soil is wet enough. ";
      }

      if (currentRaining)
      {
        reason += "Rain detected. ";
      }

      addLog(reason);
      relayOff();
      lastWateredAt = now;
    }
    else
    {
      addLog("Decision: Pump remains ON.");
    }
  }
}

// =====================================================
// WEB PAGE HELPERS
// =====================================================
String checked(bool value)
{
  return value ? "checked" : "";
}

String htmlPage()
{
  String page = R"rawliteral(
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ESP32 Watering System</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f3f4f6;
      color: #111827;
    }
    header {
      background: #111827;
      color: white;
      padding: 16px;
    }
    main {
      padding: 16px;
      max-width: 1000px;
      margin: auto;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 16px;
    }
    .card {
      background: white;
      border-radius: 12px;
      padding: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    label {
      display: block;
      margin-top: 12px;
      font-weight: bold;
    }
    input[type="number"] {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      box-sizing: border-box;
    }
    input[type="checkbox"] {
      transform: scale(1.2);
      margin-right: 8px;
    }
    button {
      border: 0;
      border-radius: 8px;
      padding: 10px 14px;
      margin-top: 12px;
      cursor: pointer;
      font-weight: bold;
    }
    .save { background: #2563eb; color: white; }
    .on { background: #16a34a; color: white; }
    .off { background: #dc2626; color: white; }
    .status-value {
      font-size: 1.25rem;
      font-weight: bold;
    }
    pre {
      background: #020617;
      color: #d1fae5;
      padding: 12px;
      border-radius: 8px;
      overflow: auto;
      min-height: 280px;
      max-height: 420px;
      white-space: pre-wrap;
    }
    .small {
      color: #4b5563;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <header>
    <h2>ESP32 Automatic Watering System</h2>
    <div>Open this page at <b>http://192.168.4.1</b> when connected to the ESP32 access point.</div>
  </header>

  <main>
    <div class="grid">
      <section class="card">
        <h3>Live Status</h3>
        <p>Mode: <span class="status-value" id="mode">-</span></p>
        <p>Pump: <span class="status-value" id="pump">-</span></p>
        <p>Soil: <span class="status-value" id="soil">-</span></p>
        <p>Rain: <span class="status-value" id="rain">-</span></p>
        <p>Temperature: <span class="status-value" id="temperature">-</span></p>
        <p>Humidity: <span class="status-value" id="humidity">-</span></p>

        <button class="on" onclick="fetch('/pump/on').then(refreshStatus)">Force Pump ON</button>
        <button class="off" onclick="fetch('/pump/off').then(refreshStatus)">Force Pump OFF</button>
        <p class="small">
          In simulation mode, the relay stays OFF unless Relay simulation mode is enabled.
        </p>
      </section>

      <section class="card">
        <h3>Parameters</h3>
        <form action="/save" method="POST">
          <label><input type="checkbox" name="simulationMode" %SIMULATION_CHECKED%> Simulation mode</label>
          <label><input type="checkbox" name="relaySimulation" %RELAY_SIMULATION_CHECKED%> Relay simulation mode</label>
          <label><input type="checkbox" name="relayActiveLow" %RELAY_LOW_CHECKED%> Relay active LOW</label>
          <label><input type="checkbox" name="rainWetBelowThreshold" %RAIN_BELOW_CHECKED%> Rain sensor is wet when raw value is below threshold</label>

          <p class="small">
            Relay simulation mode lets the real relay follow the simulated pump state.
            Use carefully if the pump is connected.
          </p>

          <label>Soil dry raw value</label>
          <input type="number" name="soilDryRaw" value="%SOIL_DRY_RAW%">

          <label>Soil wet raw value</label>
          <input type="number" name="soilWetRaw" value="%SOIL_WET_RAW%">

          <label>Rain wet threshold</label>
          <input type="number" name="rainWetThreshold" value="%RAIN_WET_THRESHOLD%">

          <label>Water when soil is below (%)</label>
          <input type="number" name="waterWhenBelow" value="%WATER_WHEN_BELOW%">

          <label>Stop when soil is above (%)</label>
          <input type="number" name="stopWhenAbove" value="%STOP_WHEN_ABOVE%">

          <label>Maximum pump time (milliseconds)</label>
          <input type="number" name="maxPumpTimeMs" value="%MAX_PUMP_TIME_MS%">

          <label>Cooldown after watering (milliseconds)</label>
          <input type="number" name="cooldownMs" value="%COOLDOWN_MS%">

          <label>Simulation dry rate per second</label>
          <input type="number" step="0.1" name="simDryRate" value="%SIM_DRY_RATE%">

          <label>Simulation rain wet rate per second</label>
          <input type="number" step="0.1" name="simRainWetRate" value="%SIM_RAIN_WET_RATE%">

          <label>Simulation pump wet rate per second</label>
          <input type="number" step="0.1" name="simPumpWetRate" value="%SIM_PUMP_WET_RATE%">

          <label>Simulation rain toggle time (milliseconds)</label>
          <input type="number" name="simRainToggleMs" value="%SIM_RAIN_TOGGLE_MS%">

          <button class="save" type="submit">Save Parameters</button>
        </form>
      </section>
    </div>

    <section class="card" style="margin-top:16px;">
      <h3>Serial Monitor Output</h3>
      <pre id="logs">Loading logs...</pre>
    </section>
  </main>

<script>
var refreshStatus = function() {
  fetch('/status')
    .then(function(response) { return response.json(); })
    .then(function(data) {
      document.getElementById('mode').textContent = data.simulationMode ? 'SIMULATION' : 'REAL HARDWARE';
      document.getElementById('pump').textContent = data.pumpOn ? 'ON' : 'OFF';
      document.getElementById('soil').textContent = data.soilPercent + '% (raw ' + data.soilRaw + ')';
      document.getElementById('rain').textContent = data.raining ? 'YES (raw ' + data.rainRaw + ')' : 'NO (raw ' + data.rainRaw + ')';
      document.getElementById('temperature').textContent = data.temperature + ' °C';
      document.getElementById('humidity').textContent = data.humidity + ' %';
    });
};

var refreshLogs = function() {
  fetch('/logs')
    .then(function(response) { return response.text(); })
    .then(function(text) {
      const box = document.getElementById('logs');
      box.textContent = text;
      box.scrollTop = box.scrollHeight;
    });
};

setInterval(refreshStatus, 2000);
setInterval(refreshLogs, 1000);
refreshStatus();
refreshLogs();
</script>
</body>
</html>
)rawliteral";

  page.replace("%SIMULATION_CHECKED%", checked(simulationMode));
  page.replace("%RELAY_SIMULATION_CHECKED%", checked(relaySimulation));
  page.replace("%RELAY_LOW_CHECKED%", checked(relayActiveLow));
  page.replace("%RAIN_BELOW_CHECKED%", checked(rainWetBelowThreshold));

  page.replace("%SOIL_DRY_RAW%", String(soilDryRaw));
  page.replace("%SOIL_WET_RAW%", String(soilWetRaw));
  page.replace("%RAIN_WET_THRESHOLD%", String(rainWetThreshold));
  page.replace("%WATER_WHEN_BELOW%", String(waterWhenBelow));
  page.replace("%STOP_WHEN_ABOVE%", String(stopWhenAbove));
  page.replace("%MAX_PUMP_TIME_MS%", String(maxPumpTimeMs));
  page.replace("%COOLDOWN_MS%", String(cooldownMs));

  page.replace("%SIM_DRY_RATE%", String(simDryRate, 1));
  page.replace("%SIM_RAIN_WET_RATE%", String(simRainWetRate, 1));
  page.replace("%SIM_PUMP_WET_RATE%", String(simPumpWetRate, 1));
  page.replace("%SIM_RAIN_TOGGLE_MS%", String(simRainToggleMs));

  return page;
}

int argToInt(String name, int currentValue)
{
  if (server.hasArg(name))
  {
    return server.arg(name).toInt();
  }
  return currentValue;
}

unsigned long argToULong(String name, unsigned long currentValue)
{
  if (server.hasArg(name))
  {
    return (unsigned long)server.arg(name).toInt();
  }
  return currentValue;
}

float argToFloat(String name, float currentValue)
{
  if (server.hasArg(name))
  {
    return server.arg(name).toFloat();
  }
  return currentValue;
}

// =====================================================
// WEB ROUTES
// =====================================================
void handleRoot()
{
  server.send(200, "text/html", htmlPage());
}

void handleStatus()
{
  String json = "{";
  json += "\"simulationMode\":" + String(simulationMode ? "true" : "false") + ",";
  json += "\"pumpOn\":" + String(pumpOn ? "true" : "false") + ",";
  json += "\"soilRaw\":" + String(currentSoilRaw) + ",";
  json += "\"soilPercent\":" + String(currentSoilPercent) + ",";
  json += "\"rainRaw\":" + String(currentRainRaw) + ",";
  json += "\"raining\":" + String(currentRaining ? "true" : "false") + ",";
  json += "\"temperature\":" + String(currentTemperature, 1) + ",";
  json += "\"humidity\":" + String(currentHumidity, 1);
  json += "}";

  server.send(200, "application/json", json);
}

void handleLogs()
{
  server.send(200, "text/plain", getLogsText());
}

void handleSave()
{
  bool oldSimulationMode = simulationMode;
  bool oldRelaySimulation = relaySimulation;
  bool oldRelayActiveLow = relayActiveLow;

  simulationMode = server.hasArg("simulationMode");
  relaySimulation = server.hasArg("relaySimulation");
  relayActiveLow = server.hasArg("relayActiveLow");
  rainWetBelowThreshold = server.hasArg("rainWetBelowThreshold");

  soilDryRaw = argToInt("soilDryRaw", soilDryRaw);
  soilWetRaw = argToInt("soilWetRaw", soilWetRaw);
  rainWetThreshold = argToInt("rainWetThreshold", rainWetThreshold);

  waterWhenBelow = argToInt("waterWhenBelow", waterWhenBelow);
  stopWhenAbove = argToInt("stopWhenAbove", stopWhenAbove);

  maxPumpTimeMs = argToULong("maxPumpTimeMs", maxPumpTimeMs);
  cooldownMs = argToULong("cooldownMs", cooldownMs);

  simDryRate = argToFloat("simDryRate", simDryRate);
  simRainWetRate = argToFloat("simRainWetRate", simRainWetRate);
  simPumpWetRate = argToFloat("simPumpWetRate", simPumpWetRate);
  simRainToggleMs = argToULong("simRainToggleMs", simRainToggleMs);

  // Keep the physical relay in a safe OFF state after changing mode/relay polarity.
  if (
      oldSimulationMode != simulationMode ||
      oldRelaySimulation != relaySimulation ||
      oldRelayActiveLow != relayActiveLow)
  {
    writeRelayHardware(false);
    pumpOn = false;
    addLog("Relay safety reset after mode/settings change.");
  }

  addLog("Settings saved from web dashboard.");

  server.sendHeader("Location", "/");
  server.send(303);
}

void handlePumpOn()
{
  addLog("Manual command from web: Force Pump ON.");
  relayOn();
  server.send(200, "text/plain", "Pump ON command sent");
}

void handlePumpOff()
{
  addLog("Manual command from web: Force Pump OFF.");
  relayOff();
  lastWateredAt = millis();
  server.send(200, "text/plain", "Pump OFF command sent");
}

void handleNotFound()
{
  server.send(404, "text/plain", "Not found");
}

// =====================================================
// SETUP
// =====================================================
void setup()
{
  Serial.begin(115200);
  delay(500);

  pinMode(RELAY_PIN, OUTPUT);
  writeRelayHardware(false); // Always start with hardware relay OFF for safety.

  analogReadResolution(12);

  if (!simulationMode)
  {
    dht.begin();
  }

  WiFi.mode(WIFI_AP_STA);
  WiFi.softAP(AP_SSID, AP_PASSWORD);
  connectToWiFiStation();

  server.on("/", HTTP_GET, handleRoot);
  server.on("/status", HTTP_GET, handleStatus);
  server.on("/logs", HTTP_GET, handleLogs);
  server.on("/save", HTTP_POST, handleSave);
  server.on("/pump/on", HTTP_GET, handlePumpOn);
  server.on("/pump/off", HTTP_GET, handlePumpOff);
  server.onNotFound(handleNotFound);
  server.begin();

  addLog("Automatic watering system started.");
  addLog(String("WiFi AP SSID: ") + AP_SSID);
  addLog("Open dashboard: http://192.168.4.1");
  addLog(String("Backend control mode: ") + (backendControlMode ? "ON" : "OFF"));

  if (simulationMode)
  {
    addLog("SIMULATION MODE ENABLED.");

    if (relaySimulation)
    {
      addLog("RELAY SIMULATION ENABLED. Real relay follows simulated pump state.");
    }
    else
    {
      addLog("Relay output will remain OFF during simulation.");
    }
  }
  else
  {
    addLog("REAL HARDWARE MODE ENABLED. Using DHT22 temperature and humidity sensor.");
  }
}

// =====================================================
// LOOP
// =====================================================
void loop()
{
  server.handleClient();

  if (simulationMode)
  {
    updateSimulation();
  }

  unsigned long now = millis();

  // Run sensor reading and watering logic every 2 seconds.
  if (now - lastLoopRun >= 2000)
  {
    lastLoopRun = now;

    readAllSensors();

    addLog("-----");
    addLog(String(simulationMode ? "[SIMULATION]" : "[REAL HARDWARE]"));
    addLog("Soil raw: " + String(currentSoilRaw) + " | Soil %: " + String(currentSoilPercent));
    addLog("Rain raw: " + String(currentRainRaw) + " | Raining: " + String(currentRaining ? "YES" : "NO"));
    addLog("Temp C: " + String(currentTemperature, 1) + " | Humidity %: " + String(currentHumidity, 1));

    if (backendControlMode)
    {
      if (now - lastBackendSync >= BACKEND_SYNC_INTERVAL_MS)
      {
        lastBackendSync = now;

        if (!syncSensorDataWithBackend())
        {
          addLog("Laravel sync failed. Running local fallback logic.");
          runWateringLogic();
        }
      }
      else
      {
        addLog("Decision: Backend control active. Waiting for next Laravel sync.");
      }
    }
    else
    {
      runWateringLogic();
    }

    addLog("Pump state: " + String(pumpOn ? "ON" : "OFF"));
  }
}
