require("dotenv").config();

const express = require("express");
const { Client, LocalAuth } = require("whatsapp-web.js");
const qrcode = require("qrcode-terminal");

const app = express();
app.use(express.json());

const PORT = process.env.PORT || 3000;
const SECRET_TOKEN = process.env.GATEWAY_SECRET_TOKEN;

if (!SECRET_TOKEN) {
    console.error(
        "[FATAL] GATEWAY_SECRET_TOKEN tidak diset di .env. Server tidak bisa jalan.",
    );
    process.exit(1);
}

// --- WhatsApp Client ---
const client = new Client({
    authStrategy: new LocalAuth({ dataPath: "./.wwebjs_auth_session" }),
    puppeteer: {
        args: ["--no-sandbox", "--disable-setuid-sandbox"],
    },
});

let isReady = false;
let lastQr = null;

client.on("qr", (qr) => {
    lastQr = qr;
    console.log(
        "\n[WA GATEWAY] Scan QR Code ini dengan WhatsApp di HP Anda:\n",
    );
    qrcode.generate(qr, { small: true });
});

client.on("ready", () => {
    isReady = true;
    lastQr = null;
    console.log("[WA GATEWAY] WhatsApp siap! Gateway berjalan.");
});

client.on("auth_failure", (msg) => {
    isReady = false;
    lastQr = null;
    console.error("[WA GATEWAY] Auth gagal:", msg);
});

client.on("disconnected", (reason) => {
    isReady = false;
    lastQr = null;
    console.warn("[WA GATEWAY] WhatsApp terputus:", reason);
});

client.initialize();

// --- Middleware Auth ---
function authMiddleware(req, res, next) {
    const authHeader = req.headers["authorization"];

    if (!authHeader || !authHeader.startsWith("Bearer ")) {
        return res
            .status(401)
            .json({
                success: false,
                message: "Unauthorized: Token tidak ada.",
            });
    }

    const token = authHeader.split(" ")[1];

    if (token !== SECRET_TOKEN) {
        return res
            .status(401)
            .json({
                success: false,
                message: "Unauthorized: Token tidak valid.",
            });
    }

    next();
}

// --- Health Check Endpoint ---
app.get("/health", (req, res) => {
    const senderNumber = isReady && client.info ? client.info.wid.user : null;

    res.json({
        success: true,
        whatsapp_ready: isReady,
        qr: lastQr,
        sender_number: senderNumber,
        message: isReady
            ? "Gateway aktif dan WhatsApp terhubung."
            : lastQr
                ? "Gateway aktif tapi WhatsApp belum scan QR."
                : "Gateway aktif, sedang menginisialisasi...",
    });
});

// --- Send Endpoint (menerima payload dari Laravel) ---
app.post("/send", authMiddleware, async (req, res) => {
    if (!isReady) {
        return res
            .status(503)
            .json({
                success: false,
                message: "WhatsApp belum siap. Scan QR Code terlebih dahulu.",
            });
    }

    const { to, message } = req.body;

    if (!to || typeof to !== "string" || to.trim() === "") {
        return res
            .status(400)
            .json({ success: false, message: 'Parameter "to" wajib diisi.' });
    }

    if (!message || typeof message !== "string" || message.trim() === "") {
        return res
            .status(400)
            .json({
                success: false,
                message: 'Parameter "message" wajib diisi.',
            });
    }

    try {
        // Normalisasi nomor: hilangkan karakter non-digit, ubah awalan 0 → 62
        let phone = to.replace(/[^0-9]/g, "");
        if (phone.startsWith("0")) {
            phone = "62" + phone.slice(1);
        }

        // Verifikasi apakah nomor terdaftar di WhatsApp
        const numberId = await client.getNumberId(phone);

        if (!numberId) {
            console.warn(
                `[WA GATEWAY] Nomor tidak terdaftar di WhatsApp: ${to} (formatted: ${phone})`,
            );
            return res.status(400).json({
                success: false,
                message: `Nomor ${to} tidak terdaftar di WhatsApp.`,
            });
        }

        const chatId = numberId._serialized;
        await client.sendMessage(chatId, message.trim());

        console.log(`[WA GATEWAY] Pesan terkirim ke ${to} (chatId: ${chatId})`);
        return res.json({ success: true, message: "Pesan berhasil dikirim." });
    } catch (error) {
        console.error("[WA GATEWAY] Gagal kirim pesan:", error.message);
        return res
            .status(500)
            .json({
                success: false,
                message: "Gagal mengirim pesan.",
                error: error.message,
            });
    }
});

// --- Start Server ---
app.listen(PORT, () => {
    console.log(`[WA GATEWAY] Server berjalan di http://localhost:${PORT}`);
    console.log("[WA GATEWAY] Menginisialisasi WhatsApp client...");
});

// --- Graceful Shutdown ---
const gracefulShutdown = async (signal) => {
    console.log(`\n[WA GATEWAY] Menerima sinyal ${signal}. Menutup koneksi secara bersih...`);
    try {
        if (client) {
            await client.destroy();
            console.log("[WA GATEWAY] WhatsApp client berhasil ditutup.");
        }
    } catch (err) {
        console.error("[WA GATEWAY] Gagal menutup WhatsApp client secara bersih:", err);
    } finally {
        process.exit(0);
    }
};

process.on("SIGINT", () => gracefulShutdown("SIGINT"));
process.on("SIGTERM", () => gracefulShutdown("SIGTERM"));

