<?php

function getDbConn($config)
{
    try {
        $conn = new PDO($config["db"]);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("ERROR");
    }
}

function runOne($conn, $test, $iter, $flagArr, $runtimes, $runId)
{
    $nameArr = explode("/", $test);
    $testName = end($nameArr);
    for ($i = 0; $i < $iter; $i++) {
        foreach ($runtimes as $runtimeName => $runtimeArr) {
            $runtime = $runtimeArr[0];
            $runtimeExtra = "";
            if (is_array($runtime)) {
                $runtimeExtra = $runtime[1];
                $runtime = $runtime[0];
            }

            $runtimeExt = $runtimeArr[1];
            foreach ($flagArr as $flagName) {
                echo "Running $test.$runtimeExt with runtime $runtimeName, flags: $flagName ($i/$iter): \n";

                $timeStr = "";
                if ($runtimeExt === "curl") {
                    $url = "$runtime$testName" . "_" . "$flagName";
                    $code = null;
                    echo $url . "\n";
                    $res = "";
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $time = microtime(true);
                    $res = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    $timeStr = $info['http_code'] === 200 ? $info['total_time'] : "FAIL ($res)";
                } else {
                    $command = "$runtime $test" . "_" . "$flagName.$runtimeExt $runtimeExtra";
                    echo $command . "\n";
                    $code = null;
                    $out = "";
                    $time = microtime(true);
                    $res = exec("/bin/bash -c \"$command\"", $out, $code);

                    $timeStr = microtime(true) - $time;
                    if ($code !== 0) $timeStr = "FAIL ($res)";
                }
                echo "Done ($timeStr)\n\n";
                $query = $conn->prepare('INSERT INTO results (run_id, runtime, test, comment, result, flags) VALUES (?,?,?,?,?,?)');
                $query->execute([$runId, $runtimeName, $testName, "RUN no. $iter", $timeStr, $flagName]);
            }
        }
    }
}
$config = json_decode(file_get_contents(__DIR__ . "/runConfig.json"), true);
if ($config == null) {
    return "config missing!";
}

$iters = isset($argv[1]) ? $argv[1] : $config["iterations"];


$tests = array_unique(array_map(function ($str) {
    return explode("_", $str)[0];
}, scandir($config["testDir"])));
$tests = array_diff($tests, [".", "..", ".gitkeep"], $config["skip"]);
$tests = array_values(array_map(function ($str) use ($config) {
    return $config["testDir"] . "/" . $str;
}, $tests));

$tests = isset($argv[2]) ? [$argv[2]] : $tests;

$conn = getDbConn($config);
$tcount = count($tests);
$run = bin2hex(random_bytes(8));

$query = $conn->prepare('INSERT INTO run (run_id, comment) VALUES (?, ?) RETURNING id');
$query->execute([$run, $config["desc"] ?? ""]);
$runDbId = $query->fetch()["id"];

echo "RUN ID IS: $run ($runDbId)\n";
$totalTime = microtime(true);
foreach ($tests as $current => $testPath) {
    $curCount = $current + 1;
    echo "Running $testPath ($curCount/$tcount): \n";
    runOne($conn, $testPath, $iters, $config["flags"], $config["runtimes"], $runDbId);
}
$totalTime = microtime(true) - $totalTime;
echo "RUN DONE ($totalTime s), can be viewed at http://localhost:7800/?run=$run\n";
