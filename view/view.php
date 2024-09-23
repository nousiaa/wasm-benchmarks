<?php
include "texGen.php";
try {
    $conn = new PDO("sqlite:/var/www/wasmdata/results.db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR");
}
$formattedData = [];
if (isset($_GET["run"])) {
    $query = $conn->prepare('SELECT id, run_id, created_at, comment FROM run where id=? OR run_id=?');
    $query->execute([$_GET["run"], $_GET["run"]]);
    $runData = $query->fetch();
    $runId = $runData["id"];

    $query = $conn->prepare('SELECT * FROM results WHERE run_id = ?');
    $query->execute([$runId]);
    $rows = $query->fetchALL();
    $rows = array_filter($rows, function ($v) {
        return is_numeric($v["result"]);
    });
    $resultData = [];
    foreach ($rows as $row) {
        if (!isset($resultData[$row["test"]])) {
            $resultData[$row["test"]] = [];
        }
        if (!isset($resultData[$row["test"]][$row["flags"]])) {
            $resultData[$row["test"]][$row["flags"]] = [];
        }
        if (!isset($resultData[$row["test"]][$row["flags"]][$row["runtime"]])) {
            $resultData[$row["test"]][$row["flags"]][$row["runtime"]] = [];
        }
        $resultData[$row["test"]][$row["flags"]][$row["runtime"]][] = $row["result"];
    }

    if (isset($_GET["dl"])) {
        $result = [];
        foreach ($resultData as $testName => &$test) {
            foreach ($test as $flagName => &$runtimes) {
                foreach ($runtimes as $runtimeName1 => &$runtime) {
                    $runtimeName = $runtimeName1 . (count($test) > 1 ? "-" . $flagName : "");
                    sort($runtime);
                    $eCount = count($runtime);
                    $md = $runtime[floor($eCount / 2)];
                    $uq = $runtime[floor(($eCount * 3) / 4)];
                    $lq = $runtime[floor(($eCount) / 4)];
                    $uw = $runtime[$eCount - 1];
                    $lw = $runtime[0];
                    if (!isset($result[$testName])) {
                        $result[$testName] = [];
                    }
                    if (!isset($result[$testName][$runtimeName])) {
                        $result[$testName][$runtimeName] = [];
                    }
                    $result[$testName][$runtimeName] = [
                        "md" => $md,
                        "uq" => $uq,
                        "lq" => $lq,
                        "uw" => $uw,
                        "lw" => $lw,
                    ];
                }
            }
        }

        if (isset($_GET["type"]) && $_GET["type"] == "env") {
            $tmpRes = [];
            foreach($result as $rk => $r) {
                foreach($r as $rk2 => $r2) {
                    if($rk2 == $_GET["dl"]) {
                        $tmpRes[$rk] = $r2;
                        break;
                    }
                }
            }
            if(isset($_GET["sort"]) && $_GET["sort"] == true) {
                uasort($tmpRes, function ($a, $b) {
                    return $b["md"] <=> $a["md"];
                });
            }
            $result = [$_GET["dl"] => $tmpRes];
        }
        
        if (isset($result[$_GET["dl"]])) {
            header('Content-Type: text/plain; charset=utf-8');
            echo generateBoxPlot($_GET["dl"], $result[$_GET["dl"]]);
        }

        die;
    }
    $formattedData = ["results" => $resultData, "run" => $runData];
} else {
    $query = $conn->prepare('SELECT id, run_id, created_at, comment FROM run where hidden=0 ORDER by id DESC');
    $query->execute([]);
    $rows = $query->fetchALL();
    foreach ($rows as $row) {
        $query = $conn->prepare('SELECT COUNT(*) as rcount FROM results where run_id=?');
        $query->execute([$row["id"]]);
        $rCount = $query->fetch()["rcount"];
        if ($rCount == 0) {
            continue;
        }
        $formattedData[] = [
            "id" => $row["id"],
            "run_id" => $row["run_id"],
            "created_at" => $row["created_at"],
            "comment" => $row["comment"],
            "rows" => $rCount
        ];
    }
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($formattedData);
