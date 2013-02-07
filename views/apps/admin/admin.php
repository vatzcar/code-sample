<?php
	class AdminView extends Views {
		public function adminDefault() {
			$config = new Config();
?>
	<div class="login-body">
		<div class="login-wrapper">
			<div class="loginbox-holder">
			<div class="loginbox">
			  <form action="<?php echo '/' . $this->request->getURI(); ?>" name="adminForm" method="post">
			    <div class="tablehead"><?php echo $this->language->_('LoginTitle'); ?></div>
<?php		if ($this->system->site_error) : ?>
			     <div class="systemerror"><?php echo $this->system->site_error_msg; ?></div>
<?php		endif; ?>
			    <div class="tablerow">
			      <div class="tablecolumnsmall"><?php echo $this->language->_('Username'); ?></div>
			      <div>
			      	<input type="text" name="uid" class="inputbox" />
			      </div>
			    </div>
			    <div class="clear"></div>
			    <div class="tablerow">
			      <div class="tablecolumnsmall"><?php echo $this->language->_('Password'); ?></div>
			      <div>
			      	<input type="password" name="paswd" class="inputbox" />
			      </div>
			    </div>
			    <div class="clear"></div>
			    <div class="tablerow">
			      <div class="tablecolumnsmall">
			        <input type="hidden" name="act" value="login" />
			      </div>
			      <div style="margin-left:7px;">
			      	<input type="submit" name="submit" value="<?php echo $this->language->_('Login'); ?>" class="loginbutton" />
			      </div>
			    </div>
			    <div class="clear"></div>
			  </form>
			</div>
			</div>
		</div>
	</div>
<?php 
		}
		
		public function adminFront() {
			$max = $this->items[0]->visited;
			
			if ($this->system->site_error) :
?>
		<div class="systemerror"><?php echo $this->system->site_error_msg; ?></div>
<?php		endif; ?>
		<div class="content-box">
			<p><?php $this->language->_('StatisticsDescription'); ?></p>
			<div class="stat-box">
<?php		foreach ($this->items as $item) : ?>
				<div class="tablerow">
					<div class="tablecolumnmaxx"><?php echo (trim($item->image_title)!='')?$item->image_title.'-'.$item->image_name:$item->image_name; ?></div>
					<div class="tablecolumnmaxx">
						<div class="visited-bar" style="width: <?php echo 280*($item->visited/$max); ?>px;"></div>
						<span class="hitno"><?php echo $item->visited; ?></span>
					</div>
					<div class="clear"></div>
				</div>
<?php 		endforeach; ?>
			</div>
		</div>
<?php 
		}
	}
?>