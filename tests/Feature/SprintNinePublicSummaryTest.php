[tests/Feature/SprintNinePublicSummaryTest.php#E487]
1:<?php
2:
3:declare(strict_types=1);
4:
5:namespace Tests\Feature;
6:
7:use App\Models\Device;
8:use App\Models\SensorReading;
9:use App\Models\ThresholdSetting;
10:use App\Models\User;
11:use Tests\Concerns\UsesMysqlTestDatabase;
12:use Tests\TestCase;
13:
14:final class SprintNinePublicSummaryTest extends TestCase
15:{
16:    use UsesMysqlTestDatabase;
17:
18:    protected function setUp(): void
19:    {
20:        parent::setUp();
21:        $this->useMysqlTestDatabase();
22:    }
23:
24:    protected function tearDown(): void
25:    {
26:        $this->rollbackMysqlTestDatabase();
27:        parent::tearDown();
28:    }
29:
30:    public function test_public_landing_page_is_accessible_and_renders_latest_sensor_data(): void
31:    {
32:        $device = $this->makeDevice();
33:
34-43:        SensorReading::query()->create([ .. ]);
44:
45:        $this->get('/')
46:            ->assertOk()
47:            ->assertSeeText('Smart Sprayer')
48:            ->assertSeeText('31.5')
49:            ->assertSeeText('70')
50:            ->assertSeeText('35')
51:            ->assertSeeText('kritis')
52:            ->assertSeeText('Sprayer Publik')
53:            ->assertSeeText('Data Publik');
54:    }
55:
56:    public function test_public_landing_hides_control_and_sensitive_information(): void
57-84:    { .. }
85:
86:    private function makeDevice(): Device
87-104:    { .. }
105:}

[50 lines elided; re-read needed ranges, e.g. tests/Feature/SprintNinePublicSummaryTest.php:34-43,57-84]