<?php
	class SitemenuView extends Views {
		public function show() {
?>
	<ul class="fe-menu">
		<li><a href="#infobox" id="infolink"></a></li>
		<li><a href="#kontaktbox" id="contactlink"></a></li>
	</ul>
	<div id="infobox">
<?php	echo $this->infoData; ?>
	</div>
	<div id="kontaktbox">
<?php	echo $this->kontaktData; ?>
	</div>
<?php
		}
	}
?>