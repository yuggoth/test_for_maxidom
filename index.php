<?php  
class GisMeteoGet {
	
	public $ruTitle = [
		"humidity" => "Влажность",
		"symbol" => "Погода",
		"temperature" => "Температура воздуха",
		"windSpeed" => "Ветер",
		"pressure" => "Давление"
	];
	
	public $ip;
	public $formatData;
	public $resultLongitude;
	public $resultLatitude;
	public $resultCity;
	private $geopluginUrl;
	private $openweathermapUrl;
	private $openweathermapKey;
	
	public function __construct() {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->openweathermapKey = '1e880035ceb331e9c8c451e9c459e155';
		$this->formatData = 'xml';
		$this->geopluginUrl = "http://www.geoplugin.net/json.gp?ip=".$this->ip;
		$this->openweathermapUrl = "http://api.openweathermap.org/data/2.5/forecast?appid=$this->openweathermapKey&mode=$this->formatData&units=metric&lang=ru&cnt=32";
		
		
		//определение координат по ip
		$ip_data = @json_decode(file_get_contents($this->geopluginUrl)); 
		//получение широты и долготы  
		if($ip_data && $ip_data->geoplugin_latitude)
		{
			$this->resultLongitude = $ip_data->geoplugin_longitude;	
			$this->resultLatitude = $ip_data->geoplugin_latitude;
			$this->resultCity = $ip_data->geoplugin_city;
		} else {
			$this->resultCity = "Город не определен";
		}		
	}

	public function getData() {
		if ($this->resultLongitude && $this->resultLatitude) {
			// запрос к апи
			$data = simplexml_load_file($this->openweathermapUrl."&lat=$this->resultLatitude&lon=$this->resultLongitude");
			// если получили данные
			if($data){
				$res = array();
				foreach ($data->forecast->time as $item) {
					$date = DateTime::createFromFormat('Y-m-d', stristr($item["from"].'','T', true))->format('d.m.Y');
					$time = substr($item["from"], strrpos($item["from"],"T")+1);
					$res[$date][$time]["symbol"] = $item->symbol["name"].'';
					$res[$date][$time]["temperature"] = $item->temperature["value"].'&nbsp;°С';
					$res[$date][$time]["windSpeed"] = $item->windSpeed["mps"].'м/с';
					$res[$date][$time]["pressure"] = round($item->pressure["value"]/1.333224).' мм. рт. ст.';	
					$res[$date][$time]["humidity"] = $item->humidity["value"].'%';
				}
				return array_slice($res, 0, 4);
			}else{
				return "Сервер не доступен";
			}
		} else {
			return "Нет координат";			
		}	
	}	
}
?>
<!DOCTYPE html>  
<html>  
<head>  
	<meta charset="utf-8">  
	<title>Название документа</title>  
    <link href="/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="/bootstrap/theme.css" rel="stylesheet">
</head>
<body>  
	<? $gis = new GisMeteoGet(); ?>
	<div class="alert alert-success">
        Ваш город: <strong><?echo $gis->resultCity;?></strong>
    </div>
	<?$data = $gis->getData();
	   if (is_array($data)) {?>
			<?foreach ($data as $day => $time) {?>
				<div class="col-sm-3">
					<div class="page-header">
						<h1><? echo $day?></h1>
					</div>
					<div class="list-group">
						<?foreach ($time as $key => $value) {?>	  
							<a href="#" class="list-group-item">
								<h4 class="list-group-item-heading"><?echo $key;?></h4>
								<p class="list-group-item-text">
									<?foreach ($value as $key => $val) {?>
										<? echo $gis->ruTitle[$key];?>: <? echo $val;?><br />
										<?if ($key === "humidity") {?>
											<div class="progress">
												<div class="progress-bar" role="progressbar" aria-valuenow="<?echo $val?>" aria-valuemin="0" aria-valuemax="100" style="width: <?echo $val?>;"><span class="sr-only"></span></div>
											</div>
										<?}?>
									<?}?>
								</p>
							</a>
						<?}?>
					</div> 
				</div>
			<?}?>
	   <?} else {
			echo $data;
	   }?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="/bootstrap/bootstrap.min.js"></script>
	<script src="/bootstrap/docs.min.js"></script> 
</body>  
</html>