<?php
function getDirContents($dir, &$results = array())
{
    foreach (scandir($dir) as $file) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $file);
        if (!is_dir($path)) {
            if (substr($path, -2, 2) === ".c") {
                $results[] = substr($path, 0, -2);
            }
        } else if (!in_array($file, [".", "..", "utilities"])) {
            getDirContents($path, $results);
        }
    }

    return $results;
}

$config = json_decode(file_get_contents(__DIR__ . "/buildConfig.json"), true);
if ($config == null) {
    return "config missing!";
}
$failList = [];
foreach($config["tests"] as $testsetTitle => $testSet) {
    $outFolder = $testSet["outDir"];
    $libPath = $testSet["lib"];
    $tests = getDirContents($testSet["base"]);
    $tcount = count($tests);
    $results = [];
    foreach ($tests as $current => $testPath) {
        $testParts = explode("/", $testPath);
        $testname = end($testParts);
        
        foreach ($config["flags"] as $flagsFileName  => $flags) {
            $curCount = $current + 1;
            echo "$testsetTitle: Building $testPath with flags $flags ($curCount/$tcount): \n";
            foreach($config["commands"] as $commandTitle => $commandArr) {
                echo "$commandTitle: ";
                $command = $commandArr["base"] . " $flags $libPath $testPath.c -o $outFolder/$testname"."_"."$flagsFileName." . $commandArr["ext"];
                $code = null;
                $out = "";
                $res = exec($command, $out, $code);
                echo ($code === 0 ? "Done" : "Fail") ."\n";
                if(!empty($res)) {
                    echo "$res \n";
                }
                
                if ($code === 0 && isset($commandArr["genWat"]) && $commandArr["genWat"] == true) {
                    exec("wasm-dis $outFolder/$testname"."_"."$flagsFileName.wasm > $outFolder/$testname"."_"."$flagsFileName.wat");
                }
                if($code !== 0) {
                    $failList[] = "$testPath.c (flags $flags) failed with $commandTitle";
                }
            }
            echo "Done\n";
            if(!empty($failList)) {
                echo "Following builds failed:\n";
                foreach($failList as $fail) {
                    echo "$fail\n";
                }
            }
        }
    }
}
