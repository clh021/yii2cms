<?php
use yii\helpers\Html;
use yii\helpers\Url;
$debugUrl=Url::toRoute(["site/debug",'id'=>$device_sn]);
if($js_script){
	$this->registerJsFile($js_script, ['depends' => \yii\web\JqueryAsset::className(),'position'=>\yii\web\View::POS_HEAD]);
}
?>
<style>
    .tab,.tab:hover{color:#fff}.col-xs-3{padding:0}.tab{display:block;text-align:center;height:40px;line-height:50px;margin:1px;font-weight:700}.tab.active{border-bottom:2px solid #ff0}ul{padding:5px;margin:0}ul li{list-style:none;padding:3px 10px}
</style>
<div class="clearfix" style="background:#2693ff;width:100%;right: 0; top: 0; z-index: 999">
	<?php foreach ($menus as $m) {?>
	<div class="col-xs-2"><a class="tab<?= $device_sn==$m ? ' active':''?>" href="<?=Url::toRoute(["site/debug",'id'=>$m])?>"><?=$m?></a>
    </div>
	<?php } echo !in_array($device_sn,$menus) ? '<div class="col-xs-2"><a class="tab active"><?=$device_sn?></a></div>' : ''; ?>
</div>
<?=$show?>