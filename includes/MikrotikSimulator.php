<?php
class MikrotikSimulator {
    private $logFile = '../logs/router.log';

    public function __construct() {
        if (!file_exists('../logs')) {
            mkdir('../logs', 0777, true);
        }
    }

    public function connect($ip, $user, $pass) {
        $this->log("Connecting to RouterOS at $ip with user $user...");
        // Simulate connection delay
        usleep(200000); // 200ms
        $this->log("Connected successfully.");
        return true;
    }

    public function blockIP($ip) {
        $this->log("COMMAND: /ip firewall address-list add list=blocked address=$ip comment='Cut Service'");
        return ['status' => 'success', 'message' => "IP $ip blocked."];
    }

    public function unblockIP($ip) {
        $this->log("COMMAND: /ip firewall address-list remove [find address=$ip list=blocked]");
        return ['status' => 'success', 'message' => "IP $ip unblocked."];
    }

    public function getTraffic($ip) {
        // Simulate random traffic data
        $tx = rand(100, 5000); // kbps
        $rx = rand(100, 10000); // kbps
        return ['tx' => $tx, 'rx' => $rx];
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}
?>
