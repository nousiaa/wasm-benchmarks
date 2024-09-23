<?php
function generatePlotImports()
{
    return "\\usepackage{pgfplots}
\\pgfplotsset{compat=1.8}
\\usepgfplotslibrary{statistics}
";
}
function generatePlotData($title, $data)
{

    $resultStr = "
\\begin{figure}[H]
\\begin{tikzpicture}
    \\begin{axis}";

    $yTicks = [];
    $ylabels = [];
    foreach (array_keys($data) as $i => $header) {
        $yTicks[]  = $i + 1;
        $ylabels[] = $header;
    }
    $yTicksStr = implode(",", $yTicks);
    $ylabelsStr = implode(",", $ylabels);
    $resultStr .= "
    [
        ylabel=runtime,
        xlabel=s,
        title=$title,
        ytick={{$yTicksStr}},
        yticklabels={{$ylabelsStr}},
    ]";


    foreach ($data as $res) {
        $md = $res["md"];
        $uq = $res["uq"];
        $lq = $res["lq"];
        $uw = $res["uw"];
        $lw = $res["lw"];
        $resultStr .= "
    \\addplot+[
        boxplot prepared={
            median=$md,
            upper quartile=$uq,
            lower quartile=$lq,
            upper whisker=$uw,
            lower whisker=$lw
        },
    ] coordinates {};";
    }

    $resultStr .= "
    \\end{axis}
\\end{tikzpicture}
\\caption{ $title }
\\end{figure}";

    return $resultStr;
}

function generateBoxPlot($title, $data)
{
    return generatePlotImports() . generatePlotData($title, $data);
}
