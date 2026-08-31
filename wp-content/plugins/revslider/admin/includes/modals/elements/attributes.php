<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
	<sr-separator keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Layer Attributes','revslider'); ?></sr-separator-title>			
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap-dep dep="not[slidebg]"><sr-input wide><input name="Layer ID" replace r="attr.id" viewchild="layer_attr" type="text" style="padding-right:40px"><span noicon="" class="sr--form--otitle"><?php _e('ID','revslider'); ?></span></sr-input>	</sr-wrap-dep>
			<sr-input wide><input name="Layer Class" replace r="attr.class" viewchild="layer_attr" type="text" style="padding-right:50px"><span noicon="" class="sr--form--otitle"><?php _e('Classes','revslider'); ?></span></sr-input>	
			<sr-wrap-dep dep="not[slidebg]"><sr-input wide><input name="Rel" replace r="attr.rel" viewchild="layer_attr" type="text" style="padding-right:40px"><span noicon="" class="sr--form--otitle"><?php _e('Rel','revslider'); ?></span></sr-input></sr-wrap-dep>
			<sr-input wide><input name="Internal Class" replace r="attr.iClass" viewchild="layer_attr" type="text" style="padding-right:90px"><span noicon="" class="sr--form--otitle"><?php _e('Internal Classes','revslider'); ?></span></sr-input>	
			<sr-wrap-dep dep="not[slidebg]">
				<sr-input wide>
					<input id="sr_layer_tabindex" replace r="attr.tabIndex" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="0" max="5000" suffix="auto" lastsuffix="" validate="true" style="padding-right:90px"><span noicon="" class="sr--form--otitle"><?php _e('Tab Index','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
						<sr-drops data-v="0">0</sr-drops>
						<sr-drops data-v="1">1</sr-drops>
						<sr-drops data-v="5">5</sr-drops>
						<sr-drops data-v="20">20</sr-drops>
						<sr-drops data-v="auto">auto</sr-drops>
					</sr-drop>
				</sr-input>
			</sr-wrap-dep>
			<sr-sp h="5"></sr-sp>			
		</sr-separator-body>
	</sr-separator>
	<sr-wrap-dep dep="is[image,video,slidebg]">
		<sr-separator keepborder>
			<sr-separator-head>
				<sr-separator-title><?php _e('Media Attributes','revslider'); ?></sr-separator-title>
				<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
			</sr-separator-head>
			<sr-separator-body>
			<sr-drop r="attr.aO" viewchild="layer_attr" wide data-v="" data-sh=".sr_layer_attr_alt" data-shdep="#eqvalue" data-onchange="editor.elements.redraw">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Alt','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="custom"><?php _e('Custom','revslider'); ?></sr-drops>
					<sr-drops data-v="ml"><?php _e('From Media Library','revslider'); ?></sr-drops>                        
					<sr-drops data-v="or"><?php _e('Original','revslider'); ?></sr-drops>                        
				</sr-drop> 
				<sr-input wide class="sr_layer_attr_alt" value="custom"><input name="Custom Alt" replace r="attr.a" viewchild="layer_attr" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('Custom Alt','revslider'); ?></span></sr-input>
				<sr-drop r="attr.tO" viewchild="layer_attr" wide data-v="" data-sh=".sr_layer_atr_title" data-shdep="#eqvalue" data-onchange="editor.elements.redraw">
					<sr-drop-view>
						<span class="sr--drop--value">None</span>
						<span class="sr--form--otitle"><?php _e('Title','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="custom"><?php _e('Custom','revslider'); ?></sr-drops>
					<sr-drops data-v="ml"><?php _e('From Media Library','revslider'); ?></sr-drops>                        
					<sr-drops data-v="or"><?php _e('Original','revslider'); ?></sr-drops>                        
				</sr-drop> 
				<sr-input wide class="sr_layer_atr_title" value="custom"><input name="Title" replace r="attr.t" viewchild="layer_attr" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('Custom Title','revslider'); ?></span></sr-input>			
				<sr-sp h="5"></sr-sp>
			</sr-separator-body>		
		</sr-separator>
	</sr-wrap-dep>
	<sr-separator>
		<sr-separator-head >
			<sr-separator-title><?php _e('Wrapper Attributes','revslider'); ?></sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>
			<sr-input wide><input name="Wrapper Id" replace r="attr.wrapId" viewchild="layer_attr" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('Wrapper ID','revslider'); ?></span></sr-input>	
			<sr-input wide><input name="Wrapper Class" replace r="attr.wrapClass" viewchild="layer_attr" type="text" style="padding-right:90px"><span noicon="" class="sr--form--otitle"><?php _e('Wrapper Classes','revslider'); ?></span></sr-input>			
			<sr-sp h="15"></sr-sp>
		</sr-separator-body>		
	</sr-separator>

	<!--<sr-separator>
		<sr-separator-head>
			<sr-separator-title>AI Instructions</sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap-dep dep="is[image,video,shape,group]">
				<sr-input wide textblock class="sr--mb--0"><textarea  name="Description" r="ai.desc" viewchild="layer_attr" style="resize:none; height:130px"></textarea><span class="sr--form--otitle" style="right:5px"><?php echo __('Content Description','revslider');?></span></sr-input>
			</sr-wrap-dep>
			<sr-input wide><input name="Layer Role" replace r="ai.role" viewchild="layer_attr" type="text"><span noicon="" class="sr--form--otitle">Layer Role</span></sr-input>
			<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.icont" viewchild="layer_attr"></sr-onoff><span>Lock Content</span></sr-wrap>
			<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.icol" viewchild="layer_attr"></sr-onoff><span>Lock Color</span></sr-wrap>
			<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.ibg" viewchild="layer_attr"></sr-onoff><span>Lock Background</span></sr-wrap>
			<sr-sp h="15"></sr-sp>
		</sr-separator-body>		
	</sr-separator>-->