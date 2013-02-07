<?php
	class FrontpageView extends Views {
		public function frontpageDefault() {
			$subgallery = array();
			$popgallery = array();
			$subgalid = 0;
			$popgalleryid = 0;
			$styledef = '';
?>
	<ul id="mycarousel" class="mainslider jcarousel-skin-ie7">
<?php
			foreach ($this->galleries as $gallery) :
				if ($gallery[3]) :
					$subgallery[$subgalid][0] = $gallery[0];
					$subgallery[$subgalid][1] = $gallery[4];
					
					$styledef .= ".carnode" . $gallery[0] . " {width:" . $this->getImageWidth('/home/bhaskar/public_html/images/thumb/large/' . $gallery[1] . '/' . $gallery[5] . '/' . $this->getFileName($gallery[2]).'.png') . "px;}\n";
?>
		<li class="carnode<?php echo $gallery[0]; ?>">
			<span class="galtitle"><?php echo $gallery[1]; ?></span>
			<img src="js/imageflow/reflect2.php?img=<?php echo '/home/bhaskar/public_html/images/thumb/large/' . $gallery[1] . '/' . $gallery[5] . '/' . $this->getFileName($gallery[2]).'.png'; ?>" alt="<?php echo $gallery[1]; ?>" rel="subgal" pgal="<?php echo $gallery[0]; ?>" nodeid="<?php echo $gallery[0]; ?>" />
		</li>
<?php
					$subgalid++;
				else :
					$popgallery[$popgalleryid] = $gallery;
					
					$styledef .= ".carnode" . $gallery[0] . " {width:" . $this->getImageWidth('/home/bhaskar/public_html/images/thumb/large/' . $gallery[1] . '/' . $this->getFileName($gallery[2]).'.png') . "px;}\n";
?>
		<li class="carnode<?php echo $gallery[0]; ?>">
			<span class="galtitle"><?php echo $gallery[1]; ?></span>
			<img id="gal-<?php echo $gallery[0]; ?>" nodeid="<?php echo $gallery[0]; ?>" src="js/imageflow/reflect2.php?img=<?php echo '/home/bhaskar/public_html/images/thumb/large/' . $gallery[1] . '/' . $this->getFileName($gallery[2]).'.png'; ?>" alt="<?php echo $gallery[1]; ?>" />
		</li>
<?php
					$popgalleryid++;
				endif;
			endforeach;
?>
	</ul>
	<div class="subgalpopup"></div>
<?php
			foreach ($subgallery as $subgal) :
?>
	<div id="gal-wrappersg-<?php echo $subgal[0]; ?>" class="subgal-wrapper-inner">
		<ul id="subgal-<?php echo $subgal[0]; ?>" class="jcarousel-skin-ie7">
<?php
				foreach ($subgal[1] as $sgal) :
					$popgallery[$popgalleryid] = $sgal;
					
					$styledef .= ".carnode" . $sgal[0] . " {width:" . $this->getImageWidth('/home/bhaskar/public_html/images/thumb/small/' . $sgal[5] . '/' . $sgal[1] . '/' . $this->getFileName($sgal[2]).'.png') . "px;}\n";
?>
			<li class="carnode<?php echo $sgal[0]; ?>">
				<span class="galtitle"><?php echo $sgal[1]; ?></span>
				<img id="gal-<?php echo $sgal[0]; ?>" nodeid="<?php echo $sgal[0]; ?>" src="js/imageflow/reflect2.php?img=<?php echo '/home/bhaskar/public_html/images/thumb/small/' . $sgal[5] . '/' . $sgal[1] . '/' . $this->getFileName($sgal[2]).'.png'; ?>" alt="<?php echo $sgal[1]; ?>" />
			</li>
<?php
					$popgalleryid++;
				endforeach; 
?>
		</ul>
	</div>
	
<?php
			endforeach;
			
			$this->addCSS($styledef);
?>
	<script type="text/javascript">
<?php 		foreach ($popgallery as $gallery) : ?>
		$("#gal-<?php echo $gallery[0]; ?>").bind('mouseup',function(){
			if (!isDragging) {
				$.fancybox([
<?php
				$i = 0;
				$total = count($gallery[4]) - 1;
				foreach ($gallery[4] as $image) : 
				//'title' : '<strong> echo str_replace(array("\r\n","\r","\n"),'',nl2br($image[1])).'</strong><br/>'.str_replace(array("\r\n","\r","\n"),'',nl2br($image[2])); ',
?>
					{
		         		'href' : 'http://<?php echo $this->request->getDomain().'/images/gallery/'; ?><?php if($gallery[5]!='') echo $gallery[5] . '/'; ?><?php echo $gallery[1].'/'.$image[0]; ?>',
		         		'title' : '<?php echo str_replace(array("\r\n","\r","\n"),'',nl2br($image[2])); ?>',
		         		'itemid' : '<?php echo $image[3]; ?>'
					} <?php if ($i < $total) { echo ','; } ?>
<?php 
					$i++;
				endforeach; 
?>
		         	],{
						'padding' : 0,
						'margin' : 0,
						'titlePosition' : 'over',
						'transitionIn' : 'elastic',
						'transitionOut' : 'elastic',
						'easingIn' : 'easeOutBack',
						'easingOut' : 'easeInBack',
						'type' : 'image'
						});
			}});
<?php 		endforeach; ?>
	</script>
<?php
		}
		
		public function counthit() {
		}
		
		private function getImageWidth ($imagename) {
			$size = getimagesize($imagename,$info);
			
			return $size[0];
		}
		
		private function getFileName ($fullname) {
			$namearr = explode('.',$fullname);
			$processedname = '';
			
			for ($i = 0; $i < count($namearr) -1 ; $i++) {
				$processedname .= $namearr[$i];
			}
			
			return $processedname;
		}
	}
?>