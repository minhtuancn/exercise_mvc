{extends file="templates/layout.tpl"}

{block name=title}Dash Board{/block}

{block name=css}
	<link rel="stylesheet" type="text/css" href="themes/default/common.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="libraries/javascript/jquery/plugins/jqueryui/themes/base/jquery-ui.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="Module/DashBoard/templates/dashboard.css" media="screen" />
{/block}

{block name=javascript}
    <script type="text/javascript" src="themes/common.js"></script>
    <script type="text/javascript" src="libraries/javascript/date.js"></script>
    <script type="text/javascript" src="libraries/javascript/jquery/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="libraries/javascript/jquery/plugins/jqueryui/jquery-ui-1.8.1.min.js"></script>

    <!-- BEGIN: jqplot -->
    <!--[if IE]><script src="libraries/javascript/jqplot/excanvas.min.js"></script><![endif]-->
    <link rel="stylesheet" type="text/css" href="libraries/javascript/jqplot/jquery.jqplot.css" />
    <script type="text/javascript" src="libraries/javascript/jqplot/jquery.jqplot.js"></script>
    <script type="text/javascript" src="libraries/javascript/jqplot/plugins/jqplot.categoryAxisRenderer.js"></script>
    <script type="text/javascript" src="libraries/javascript/jqplot/plugins/jqplot.pointLabels.js"></script>

    <script type="text/javascript" src="libraries/javascript/jqplot/plugins/jqplot.canvasTextRenderer.js"></script>
    <script type="text/javascript" src="libraries/javascript/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js"></script>

    <script type="text/javascript" src="libraries/javascript/jqplot/plugins/jqplot.barRenderer.js"></script>

    <!-- END: jqplot -->

    <script type="text/javascript" src="Module/DashBoard/templates/dashboard.js"></script>
{/block}

{block name=body}
<center>
<h1>Dash Board</h1>
</center>

<center>
<div class="jqplot" style="margin:20px;width:800px;height:240px;"  id="graphs_plans"></div>

<span class="plans_back">back</span>
<span class="plans_forward">forward</span>
<div style="margin:20px;width:800px;height:240px;" id="details_plans"></div>

</center>

{/block}
