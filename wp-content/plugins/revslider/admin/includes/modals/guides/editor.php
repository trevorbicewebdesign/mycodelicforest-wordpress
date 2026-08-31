<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-- WELCOME TO THE EDITOR GUIDE MODAL -->
<sr-modal id="sr_editor_guide" class="sr--editor--guide--modal">
    <sr-modal-header>
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Welcome to SR7','revslider');?></h2>
        <p class="sr--text"><?php echo __("Let's do a quick walkthrough of the main editor sections.<br>Perfect for getting familiar with the interface and understanding how<br>everything fits together.",'revslider');?></p>        
    </sr-modal-header>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-tour-selector style="margin-right:10px">
        <sr-tour-selector-media tourtext></sr-tour-selector-media>
        <sr-tour-selector-content>
            <sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0 sr--cta--big"><?php echo __('Start Tooltip Tour','revslider');?><span class="sr--extra--text"><?php echo __('2-3 minutes','revslider');?></span></sr-button>
        </sr-tour-selector-content>
    </sr-tour-selector><!--
    --><sr-tour-selector animation>
        <sr-tour-selector-media tourvideo></sr-tour-selector-media>
        <sr-tour-selector-content>
            <sr-button id="eguide_video_btn" data-action="eguide.step" data-aparams="99" animation="" class="sr--cta sr--mb--0 sr--cta--big"><?php echo __('Watch Video Tour','revslider');?><span class="sr--extra--text"><?php echo __('5x ~3 minutes','revslider');?></span></sr-button>
        </sr-tour-selector-content>
    </sr-tour-selector>
        
    <sr-wrap class="sr--editor--buttons">        
        <sr-button data-action="eguide.cancel" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Maybe Later','revslider');?></sr-button>        
    </sr-wrap>
    
</sr-modal>


<!-- CANCEL TO THE EDITOR GUIDE MODAL -->
<sr-modal id="sr_editor_cancel" class="sr--editor--guide--modal" center>
    <sr-popup-closer data-action="B.popUp.hideAll"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left" cancel></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Tour Cancelled','revslider');?></h2>
        <p class="sr--text"><?php echo __('No worries — you can restart the tour anytime under Settings in the top-left corner of the editor.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons">        
        <sr-button data-action="B.popUp.hideAll" primary="" class="sr--cta sr--mb--0"><?php echo __('Ok. Thanks','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<sr-modal id="sr_editor_step99" class="sr--editor--guide--modal" video center>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-guide-video><iframe src="https://player.mediadelivery.net/embed/578939/221cfa0c-8c88-4d6c-91d3-729adabe5198?autoplay=true&amp;loop=false&amp;muted=false&amp;preload=true&amp;responsive=true" loading="lazy" allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;" allowfullscreen="true"></iframe></sr-guide-video>
    <div class="videoguideproxy" style="position:absolute; visibility:hidden"></div>
    <sr-video-guide-nav-wrap id="sr-video-guide-nav-wrap">
        <sr-video-guide-nav data-action="eguide.videoChange" data-aparams="dashboard" class="selected" id="sr_guide_video_dashtour">
            <h5><?php echo __('Plugin Dashboard Tour','revslider');?></h5>
            <p><?php echo __('Manage modules, access templates, configure global settings, and prepare your projects for creation.','revslider');?></p>
            <sr-video-infos>
                <sr-button white="" class="sr--cta sr--oicon sr--mr--5 sr--mb--0"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button>
                <span>5:10</span>
            </sr-video-infos>
        </sr-video-guide-nav><!--
        --><sr-video-guide-nav data-action="eguide.videoChange" data-aparams="intro"  id="sr_guide_video_intro">
            <h5><?php echo __('Introduction to SR7','revslider');?></h5>
            <p><?php echo __('A guided tour of the editor’s<br>main tools and how<br>everything fits together.','revslider');?></p>
            <sr-video-infos>
                <sr-button white="" class="sr--cta sr--oicon sr--mr--5 sr--mb--0"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button>
                <span>2:45</span>
            </sr-video-infos>
        </sr-video-guide-nav><!--
    --><sr-video-guide-nav data-action="eguide.videoChange" data-aparams="design" id="sr_guide_video_designmode">
            <h5><?php echo __('Design Mode','revslider');?></h5>
            <p><?php echo __('Build your layout, place content, and style every part of your design.','revslider');?></p>
            <sr-video-infos>
                <sr-button white="" class="sr--cta sr--oicon sr--mr--5 sr--mb--0"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button>
                <span>3:36</span>
            </sr-video-infos>
        </sr-video-guide-nav><!--
        --><sr-video-guide-nav data-action="eguide.videoChange" data-aparams="animation" id="sr_guide_video_animationmode">
            <h5><?php echo __('Animation Mode','revslider');?></h5>
            <p><?php echo __('Bring your layers to life<br>with timelines, motion, and effects.','revslider');?></p>
            <sr-video-infos>
                <sr-button white="" class="sr--cta sr--oicon sr--mr--5 sr--mb--0"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button>
                <span>3:36</span>
            </sr-video-infos>
        </sr-video-guide-nav><!--
        --><sr-video-guide-nav data-action="eguide.videoChange" data-aparams="action" id="sr_guide_video_actionmode">
            <h5><?php echo __('Action Mode & Scenes','revslider');?></h5>
            <p><?php echo __('Add clicks, links, and interactions to turn designs into experiences.','revslider');?></p>
            <sr-video-infos>
                <sr-button white="" class="sr--cta sr--oicon sr--mr--5 sr--mb--0"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button>
                <span>4:45</span>
            </sr-video-infos>
        </sr-video-guide-nav><!--
        --><sr-video-guide-nav data-action="eguide.videoChange" data-aparams="ai" id="sr_guide_video_aimode" style="margin-right:0px">
            <h5><?php echo __('AI Features','revslider');?></h5>
            <p><?php echo __('SR7 AI image and text generation can transform any module in seconds.','revslider');?></p>
            <sr-video-infos>
                <sr-button white="" class="sr--cta sr--oicon sr--mr--5 sr--mb--0"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button>
                <span>2:51</span>
            </sr-video-infos>
        </sr-video-guide-nav>      
    </sr-video-guide-nav-wrap>
</sr-modal>

<!-- STEP 1 -->
<sr-modal id="sr_editor_step1" class="sr--editor--guide--modal" data-refto="sr_est_settings" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Module Configuration','revslider');?></h2>
        <p class="sr--text"><?php echo __('Configure your module’s overall setup  — title, layout, size, type, and global options like parallax, navigation, and addons.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">1/11</p>        
        <sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 2 -->
<sr-modal id="sr_editor_step2" class="sr--editor--guide--modal" data-refto="sr_est_slides" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Slides','revslider');?></h2>
        <p class="sr--text"><?php echo __('Add, duplicate, delete, and reorder slides. Manage slide-specific settings like thumbnails, timing, progress, and scheduling.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">2/11</p>
        <sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 3 -->
<sr-modal id="sr_editor_step3" class="sr--editor--guide--modal" data-refto="editor_timeline_tab" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Timeline','revslider');?></h2>
        <p class="sr--text"><?php echo __('Enter animation mode to control how layers animate. Assign presets or fine-tune keyframes using Preset or Advanced Mode for detailed timing and motion control.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">3/11</p>
        <sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 4 -->
<sr-modal id="sr_editor_step4" class="sr--editor--guide--modal" data-refto="sr_est_elements" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Layers','revslider');?></h2>
        <p class="sr--text"><?php echo __('Browse, search, and reorder all layers in your slide. Perfect for managing nested groups, filtering elements, previewing scenes, and accessing bulk editing tools.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">4/11</p>
        <sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 5 -->
<sr-modal id="sr_editor_step5" class="sr--editor--guide--modal" data-refto="sr_est_add" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Add Layer','revslider');?></h2>
        <p class="sr--text"><?php echo __('Insert new layers into your slide — text, images, buttons, shapes, media, icons, and navigation elements.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">5/11</p>
        <sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 6 -->
<sr-modal id="sr_editor_step6" class="sr--editor--guide--modal" data-refto="sr_est_devices" data-offsetx="-7" data-offsety="5" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Device Selector','revslider');?></h2>
        <p class="sr--text"><?php echo __('Switch between and activate device breakpoints. Each breakpoint lets you customize elements specifically for that screen size.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">6/11</p>
        <sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 7 -->
<sr-modal id="sr_editor_step7" class="sr--editor--guide--modal" data-refto="sr_publish_group" data-offsetx="0" data-offsety="0" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Publish, Preview & Save','revslider');?></h2>
        <p class="sr--text"><?php echo __('Save your work, preview the module in action, and access all embed options to publish it on your site.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">7/11</p>
        <sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 8 -->
<sr-modal id="sr_editor_step8" class="sr--editor--guide--modal" data-refto="sr_est_undo" data-offsetx="-25" data-offsety="0" lefttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Undo & Redo','revslider');?></h2>
        <p class="sr--text"><?php echo __('Quickly step backward or forward through your recent edits, making it easy to correct mistakes or revisit previous changes.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">8/11</p>
        <sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>


<!-- STEP 9 -->
<sr-modal id="sr_editor_step9" class="sr--editor--guide--modal" data-refto="sr_element_editing_selector" data-offsetx="35" data-offsety="-10" righttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Design Mode','revslider');?></h2>
        <p class="sr--text"><?php echo __('Quickly step backward or forward through your recent edits, making it easy to correct mistakes or revisit previous changes.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">9/11</p>
        <sr-button animation="" data-action="eguide.step" data-aparams="99" data-video="design"  class="sr--cta sr--oicon sr--mb--0 sr--mr--10"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button><!--
        --><sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 10 -->
<sr-modal id="sr_editor_step10" class="sr--editor--guide--modal" data-refto="sr_element_animation_selector" data-offsetx="35" data-offsety="-10" righttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Animation Mode','revslider');?></h2>
        <p class="sr--text"><?php echo __('Quickly step backward or forward through your recent edits, making it easy to correct mistakes or revisit previous changes.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">10/11</p>
        <sr-button animation="" data-action="eguide.step" data-aparams="99" data-video="animation"  class="sr--cta sr--oicon sr--mb--0 sr--mr--10"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button><!--
        --><sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.step" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('Next','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>

<!-- STEP 11-->
<sr-modal id="sr_editor_step11" class="sr--editor--guide--modal" data-refto="sr_element_action_selector" data-offsetx="35" data-offsety="-10" righttop>
    <sr-popup-closer data-action="eguide.cancel"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-closer>
    <sr-wrap inline class="sr--editor--guide--left"></sr-wrap><!--
    --><sr-wrap inline class="sr--editor--guide--right">
        <h4 class="sr--editor--pretitle"><?php echo __('Editor Tour','revslider');?></h4>
        <h2 class="sr--editor--title"><?php echo __('Action Mode','revslider');?></h2>
        <p class="sr--text"><?php echo __('Quickly step backward or forward through your recent edits, making it easy to correct mistakes or revisit previous changes.','revslider');?></p>        
    </sr-wrap>
    <sr-wrap class="sr--editor--buttons"> 
        <p class="sr-guide-steps-text">11/11</p>
        <sr-button animation="" data-action="eguide.step" data-aparams="99" data-video="ation" class="sr--cta sr--oicon sr--mb--0 sr--mr--10"><svg class="sr--icon" width="6.928" height="10" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-button><!--
        --><sr-button data-action="eguide.step" data-aparams="prev" clean="" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Previous','revslider');?></sr-button><!--
    --><sr-button data-action="eguide.close" data-aparams="next" primary="" class="sr--cta sr--mb--0"><?php echo __('End Tour','revslider');?></sr-button>
    </sr-wrap>
</sr-modal>