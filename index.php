<?php
session_start();

class Website
{
    private $head;
    private $body;
    private $foot;
    private $json = null;

    public function __construct($title)
    {

        $this->head = '
        <!DOCTYPE html>
            <html lang="de">
                <head>
                <meta charset="utf-8">
                <meta name="viewport"content="width=device-width, initial-scale=1.0">
    <title>'.$title.'</title>
    <script type="text/javascript" src="https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
    <script type="text/javascript" src="https://cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js"></script>
  </head><body>';

        $this->foot = "
</body></html>";

        $this->body = "";
    }

    public function getHtml()
    {
		if ($this->json !== null) {
			header("Content-Type: application/json;charset=utf-8");
			return json_encode($this->json);
		}

		return $this->head . $this->body . $this->foot;    
    } 

	public function readCSV($file, $maxRows)
	{
		$obj = [];
		$row = 0;
		if (($handle = fopen($file, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$row++;
				$obj[] = (object) array(label => "$file", value => "$data[0]");

				if ($row >= $maxRows) {
					return $obj;
				}
			}

			fclose($handle);
		}

		return $obj;
	}

	public function addChart()
	{
		$this->body.='<script>';
		$this->body.='
			const chartConfig = {
			  type: "column2d",
			  renderAt: "chart-container",
			  width: "50%",
			  height: "300",
			  dataFormat: "json",
			  dataSource: {
				chart: {
				  caption: "Irgendwas mit LTE-Routern",
				  subCaption: "ist noch in Arbeit",
				  xAxisName: "Namen der Router",
				  yAxisName: "Geschwindigkeit (Mbit / s)",
				  numberSuffix: "",
				  theme: "fusion"
				},
				data: chartData
			  }
			};
		';

		$this->body.='
			FusionCharts.ready(function(){
				var fusioncharts = new FusionCharts(chartConfig);
				fusioncharts.render();
			});
		';
		$this->body.='</script>';
		$this->body.='<div id="chart-container">Die Grafik lädt...</div>';
	}

	private function postRecord()
	{
		$filename = $_POST['testobject'].'.csv';
		$record = $_POST['record'];

		$filename = strtolower($filename);
		// Remove anything which isn't a word, number or any of the following caracters -_~,;[]().
		$filename = preg_replace("([^\w\d\-_~,;\[\]\(\).])", '', $filename);
		// Remove any runs of periods
		$filename = preg_replace("([\.]{2,})", '', $filename);

		$time =  $date = date('Y-m-d_H:i:s ', time());

		$handle = fopen($filename, 'a');

		$line = $time.','.$record.PHP_EOL;

		fwrite($handle, $line);

		fclose($handle);  
	}

	private function getDevices()
	{
		$path = '.';
		$this->json = [];

		if (is_dir($path)) {
			if ($handle = opendir($path)) {
				while (($file = readdir($handle)) !== false) {
					if (filetype($file) === 'file' and pathinfo($file)['extension'] === 'csv') {
						$this->json[] = (object) array(file => $file, size => filesize($file), modified => date(DATE_ISO8601, stat($file)['mtime']));
					}
				}
				closedir($handle);
			}
		}
	}

	private function getDevice($filename)
	{
		$filename = strtolower($filename);
		// Remove anything which isn't a word, number or any of the following caracters -_~,;[]().
		$filename = preg_replace("([^\w\d\-_~,;\[\]\(\).])", '', $filename);
		// Remove any runs of periods
		$filename = preg_replace("([\.]{2,})", '', $filename);

		// 2d * 24h * 60min / 5min
		$this->json = $this->readCSV($filename, 576);
	}

	private function getDashboardPage()
	{
		$content = '';
		$verzeichnis = ".";
		$content .= "<ol>";

		$content .= "<script>var chartData = [];</script>";

		if ( is_dir ( $verzeichnis )){
			if ( $handle = opendir($verzeichnis) ){
				while (($file = readdir($handle)) !== false){
					if (filetype( $file ) =='file' and pathinfo($file)['extension']=='csv'){
						$content .= "<li>";
						$content .= '
									<a href="./'.$file.'" target="_blank">'
										.$file.'
									</a> 
										size: '.(filesize($file)/1024).'
										kB, last update: '.date(DATE_RFC822, stat($file)['mtime'])."
									</li>\n";
						$content .= "<script>chartData.push(" . json_encode($this->readCSV($file, 1)[0]) . ");</script>";
					}
				}
				closedir($handle);
			}
		}
		$content .= "</ol>";

		$this->body.='
		<style>
			.wrap { background: slateblue; }
			.spalte-1 { float: left; width: 49%; background: black; padding: 1em;}
			.spalte-2 { float: left; width: 1%; background: black; }
			.spalte-3 { float: left; width: 49%; background: grey; }
			body {
				background-color: black;
				color: lightgreen;
			}
		</style>
		<div class="wrap">
			<div class="spalte-1">
			<h1>LTE-Test</h1>
			 Diese Seite sammelt CSV-Daten eines automatisierten LTE-Testes.
			';


		$this->body.='</div>

			<div class="spalte-3">
				'.$content.' 
			</div>

		</div>

		';

		$this->addChart();
	}

	private function getLoginPage()
	{
		$this->body .= "<form method='GET'> 
		<input name='field' type='password'>
		<br>
		<input type='submit' value='Login'>
		</form>" ;
	}

	public function route()
    {
		$passphraseWeb = 'login_key';
		$passphraseAPI = 'secretAPIkey';

		if ($_GET['field'] === $passphraseWeb) {
			$_SESSION['login'] = TRUE;
        }

		$isLoggedIn = $_SESSION['login'] === TRUE;

		if ($_POST['api'] === $passphraseAPI) {
			$this->postRecord();
        }
        elseif ($_GET['api'] === 'devices' && $isLoggedIn) {
			$this->getDevices();
        }
        elseif ($_GET['api'] === 'device' && array_key_exists('file', $_GET) && $isLoggedIn) {
			$this->getDevice($_GET['file']);
        }
        elseif ($isLoggedIn) {
			$this->getDashboardPage();
        }
        else {
			$this->getLoginPage();
        }
    
    }
}
$mySite = new Website("ltetest");

$mySite->route();

echo $mySite->getHtml();

?>

