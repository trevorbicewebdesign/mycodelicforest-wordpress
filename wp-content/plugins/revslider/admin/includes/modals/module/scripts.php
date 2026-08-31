<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

$tags = [
    'sr7-module' => 'Main Module Wrapper',
    'sr7-content' => 'Module Content',
    'sr7-carousel' => 'Carousel Container',
    'sr7-slide' => 'Slide Container',
    'sr7-row' => 'Row Container',
    'sr7-col' => 'Column Container',
    'sr7-shp' => 'Shape Layer',
    'sr7-txt' => 'Text Layer',
    'sr7-img' => 'Image Layer'
];

$methods = [
    [
        'name' => 'Start Slider',
        'info' => 'Start Manual Module if waitApi set to true',
        'func' => 'revapi.start()',
        'code' => 'revapi.start(); // Start Manual Module if waitApi set to true'
    ],
    [
        'name' => 'Pause Slider',
        'info' => 'Pause Progress',
        'func' => 'revapi.pause()',
        'code' => 'revapi.pause(); // Pause Progress'
    ],
    [
        'name' => 'Forced Pause',
        'info' => 'Forced Pause Progress',
        'func' => 'revapi.forcedPause()',
        'code' => 'revapi.forcedPause(); // Forced Pause Progress (will not auto resume in viewport until resume() method call)'
    ],
    [
        'name' => 'Resume Slider',
        'info' => 'Resume Progress',
        'func' => 'revapi.resume()',
        'code' => 'revapi.resume(); // Resume Progress'
    ],
    [
        'name' => 'Previous Slide',
        'info' => 'Call Next Slide',
        'func' => 'revapi.prevSlide()',
        'code' => 'revapi.prevSlide(); // Call Next Slide'
    ],
    [
        'name' => 'Next Slide',
        'info' => 'Call Previous Slide',
        'func' => 'revapi.nextSlide()',
        'code' => 'revapi.nextSlide(); // Call Previous Slide'
    ],
    [
        'name' => 'External Scroll',
        'info' => 'Scroll to "y" position',
        'func' => 'revapi.scroll(offset)',
        'code' => 'revapi.scroll(offset); //Scroll to "y" position'
    ],
    [
        'name' => 'Max Slides',
        'info' => 'Amount of Slides (Not only through navigation available slides)',
        'func' => 'revapi.maxSlide()',
        'code' => 'revapi.maxSlide(); // Amount of Slides (Not only through navigation available slides)'
    ],
    [
        'name' => 'Last Slide',
        'info' => 'The last available Slide due Navigation',
        'func' => 'revapi.revlastslide()',
        'code' => 'revapi.lastSlide(); // The last available Slide due Navigation'
    ],
    [
        'name' => 'Go To Slide',
        'info' => 'Show Slide. Possible values: last, first, random, id, +2, -1, 0-100.',
        'func' => 'revapi.showSlide(2)',
        'code' => 'revapi.showSlide(2); // Show Slide. Possible Values:&#013;            /*"last" - Last Slide&#013;            "first" - First Slide&#013;            "random" - Random Slide&#013;            "id" - Slide with ID&#013;            "+2" - 2 Slide furthet&#013;            "-1" - 1 Slide back&#013;            0-100 - Slide with Index (0 - xx)  */'
    ],
    [
        'name' => 'Current Slide',
        'info' => 'Get Current Slide => {index: 2, key: 281, order: 3}',
        'func' => 'revapi.currentSlide()',
        'code' => 'revapi.currentSlide(); // Get Current Slide => {index: 2, key: 281, order: 3}'
    ],
    [
        'name' => 'Redraw Slider',
        'info' => 'Redraw the Module',
        'func' => 'revapi.revredraw()',
        'code' => 'revapi.redraw(); //Redraw the Module'
    ],
    [
        'name' => 'Kill Slider',
        'info' => 'Kill and Remove Module',
        'func' => 'revapi.kill()',
        'code' => 'revapi.kill(); // Kill and Remove Module'
    ],
    [
        'name' => 'Go To Frame',
        'info' => 'Play Layer with Scene (Scene i.e. "in" , "out", "scene_1", "scene_2" etc)',
        'func' => 'revapi.playScene(##layerid##,##scene##)',
        'code' => 'revapi.playScene(layerid,scene); // Play Layer with Scene (Scene i.e. "in" , "out", "scene_1", "scene_2" etc)'
    ],
    [
        'name' => 'Remove Slide',
        'info' => 'Remove Single Slide from Loop',
        'func' => 'revapi.removeSlide(slidekey)',
        'code' => 'revapi.removeSlide(slidekey); // Remove Single Slide from Loop'
    ]
];

$events = [
    'Modal Loaded' => 'document.addEventListener("sr.modal.loaded", function (e) {&#013;    console.log("sr.modal.loaded",e.id,e.alias);&#013;});',
    'Modal Open' => 'document.addEventListener("sr.modal.open", function (e) {&#013;    console.log("sr.modal.open",e.id,e.alias);&#013;});',
    'Modal Close' => 'document.addEventListener("sr.modal.close", function (e) {&#013;    console.log(e.id);&#013;    console.log("sr.modal.close",e.id,e.alias);&#013;});',
    'Module Ready' => 'document.addEventListener("sr.module.ready", function (e,id) {&#013;    console.group("sr.module.ready",e.id);&#013;    console.log("This Id:",revapi.id);&#013;    console.groupEnd();&#013;});',
    'Slide After Change' => 'document.addEventListener("sr.slide.afterChange", function (e) {&#013;    console.group("sr.slide.afterChange",e.id);&#013;    console.log("This Id:",revapi.id);&#013;    console.log("Current:",e.current);&#013;    console.log("Previous:",e.previous);&#013;    console.groupEnd();&#013;});',
    'Slide Ended' => 'document.addEventListener("sr.slide.ended", function (e) {&#013;    console.group("sr.slide.ended",e.id);&#013;    console.log("This Id:",revapi.id);&#013;    console.log("Current:",e.current);&#013;    console.log("Next:",e.next);&#013;    console.groupEnd();&#013;});',
    'Slide Before Change' => 'document.addEventListener("sr.slide.beforeChange", function (e) {&#013;    console.group("sr.slide.beforeChange",e.id);&#013;    console.log("This Id:",revapi.id);&#013;    console.log("Current:",e.current);&#013;    console.log("Next:",e.next);&#013;    console.groupEnd();&#013;});',
    'Slide Pause' => 'document.addEventListener("sr.slide.pause", function (e) {&#013;    console.group("sr.slide.pause",e.id);&#013;    console.log("This Id:",revapi.id);&#013;    console.log(e);&#013;    console.groupEnd();&#013;});',
    'Slide Resume' => 'document.addEventListener("sr.slide.resume", function (e) {&#013;    console.group("sr.slide.resume",e.id);&#013;    console.log("This Id:",revapi.id);&#013;    console.log(e);&#013;    console.groupEnd();&#013;});',
    'Media Update' => 'document.addEventListener("sr.media.update", function(e) {&#013;    console.group("sr.media.update",e.id, e.skey, e.layerid);&#013;    console.log("player",e.player);&#013;    console.log("layer",e.layer);&#013;    console.log("options" ,  e.options);&#013;    console.log("type" ,  e.mediatype);&#013;    console.log("id" ,  e.id);&#013;    console.log("layerid" ,  e.layerid);&#013;    console.log("skey" ,  e.skey);&#013;    console.log("state" , e.state);&#013;    console.groupEnd();&#013;});',
    'Layer Action' => 'document.addEventListener("sr.layer.action", function (e) {&#013;    console.group("sr.layer.action",e.id, e.layerid);&#013;    console.log("eventtype",e.eventtype);&#013;    console.log("caller",e.caller);&#013;    console.log("scene",e.scene);&#013;    console.log("frame",e.frame);&#013;    console.log("c",e.c);&#013;    console.log("layer",e.layer);&#013;    console.log("layertype",e.layertype);&#013;    console.log("layersettings", e.layersettings);&#013;    console.groupEnd();&#013;});',
    'Module Finished' => 'document.addEventListener("sr.module.finished", function(e) {&#013;    console.log("sr.module.finished",e.id);&#013;});'
];

$examples = [
    'External Nav Buttons' => '// Replace "prev-btn" / "next-btn" with your HTML element IDs&#013;document.addEventListener("sr.module.ready", function(e) {&#013;    document.getElementById("prev-btn").addEventListener("click", function() {&#013;        revapi.prevSlide();&#013;    });&#013;    document.getElementById("next-btn").addEventListener("click", function() {&#013;        revapi.nextSlide();&#013;    });&#013;});',
    'Custom Keyboard Navigation' => '// Gaming-style A / D keyboard navigation (change keys to any character you prefer)&#013;// A = previous slide, D = next slide&#013;document.addEventListener("keydown", function(e) {&#013;    if (e.key === "a" || e.key === "A") revapi.prevSlide();&#013;    if (e.key === "d" || e.key === "D") revapi.nextSlide();&#013;});',
    'Slide Counter (X of Y)' => 'document.addEventListener("sr.module.ready", function(e) {&#013;    // Replace "slide-counter" with the ID of your counter element&#013;    var el = document.getElementById("slide-counter");&#013;&#013;    // Updates the counter on every slide change&#013;    document.addEventListener("sr.slide.afterChange", function(e) {&#013;        if (e.id !== revapi.id) return;&#013;        el.textContent = (e.current.index + 1) + " / " + revapi.maxSlide();&#013;    });&#013;});',
    'Go To Slide (External)' => 'document.addEventListener("sr.module.ready", function(e) {&#013;    // Add data-goto-slide="N" to any button or link on the page&#013;    // N is the slide index: 0 = first slide, 1 = second, etc.&#013;    document.querySelectorAll("[data-goto-slide]").forEach(function(btn) {&#013;        btn.addEventListener("click", function() {&#013;            revapi.showSlide(parseInt(this.getAttribute("data-goto-slide")));&#013;        });&#013;    });&#013;});',
    'GA4 / GTM Analytics' => '// Requires Google Tag Manager or gtag.js already loaded on the page&#013;document.addEventListener("sr.slide.afterChange", function(e) {&#013;    if (e.id !== revapi.id) return;&#013;    if (window.dataLayer) {&#013;        window.dataLayer.push({&#013;            event:       "rs_slide_view",&#013;            slider_id:   e.id,            // module identifier&#013;            slide_index: e.current.index, // 0-based position&#013;            slide_id:    e.current.id     // unique slide ID&#013;        });&#013;    }&#013;});',
    'Style Page per Slide' => '// Adds class "sr-slide-0", "sr-slide-1" etc. to <body> on each change&#013;// Use .sr-slide-0 { background: red; } in CSS to style other page elements per slide&#013;document.addEventListener("sr.slide.afterChange", function(e) {&#013;    if (e.id !== revapi.id) return;&#013;    document.body.className = document.body.className&#013;        .replace(/\bsr-slide-\d+\b/g, "")&#013;        .trim();&#013;    document.body.classList.add("sr-slide-" + e.current.index);&#013;});',
    'Reduce Motion (Accessibility)' => '// Pauses the slider for users who prefer reduced motion (OS accessibility setting)&#013;if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {&#013;    document.addEventListener("sr.slide.afterChange", function(e) {&#013;        if (e.id !== revapi.id) return;&#013;        revapi.forcedPause();&#013;    });&#013;}',
    'Scroll to Next Section' => '// Smoothly scrolls the page to a target element once all slides finish&#013;// Add an id like "next-section" to the element you want to scroll to&#013;document.addEventListener("sr.module.finished", function(e) {&#013;    if (e.id !== revapi.id) return;&#013;    var target = document.getElementById("next-section");&#013;    if (target) {&#013;        target.scrollIntoView({ behavior: "smooth" });&#013;    }&#013;});'
];

?>
<sr-modal id="sr_module_scripts" class="sr--no--padding sr--panel--leftsidebar" view="modulescripts" style="width:640px" hasslideout="sr7-module-slideout-wrap">
    <sr-options-menu fourperrow>
        <sr-nav-btn data-sr-tabc="sr_moscript_css" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="16.425" height="12.675"><use xlink:href="#Dashboard_HTML"></use></svg></sr-icon-wrap><span><?php echo __('Custom CSS','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_moscript_js" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="16.425" height="12.675" transform="translate(0,3)"><use xlink:href="#Dashboard_HTML"></use></svg></sr-icon-wrap><span><?php echo __('Custom Javascript','revslider');?></span></sr-nav-btn>        
    </sr-options-menu>
    <sr-modal-content style="padding:0px">
        <sr7-module-slideout-wrap view="global_css_codesnippets" class="sr--tab--content sr--open" data-rel-id="sr_moscript_css" id="global_css_codesnippets">
            <sr-options-menu fourperrow>
                <sr-nav-btn data-sr-tabc="sr_snippets_tags" data-tab-target-group="gcss" class="sr--tab--call"><span><?php echo __('Tags','revslider');?></span></sr-nav-btn>
            </sr-options-menu>
            <sr7-module-slideout-content>
                <sr-wrap view="codesnippets_tags" viewchild="global_css_codesnippets" data-tab-target-group="gcss" class="sr--tab--content" id="sr_snippets_tags">
                    <sr-wrap class="sr--tab--content--wrap">
                        <?php foreach ($tags as $tag => $label) : ?>
                            <sr-wrap class="sr--with--button">
                                <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                <sr-button data-action="B.insertSnippet" clean data-aparams='<?php echo $tag; ?> {&#013;}&#013;' data-t="codes.css" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                            </sr-wrap>
                        <?php endforeach; ?>
                    </sr-wrap>
                </sr-wrap>
            </sr7-module-slideout-content>
        </sr7-module-slideout-wrap>
        <sr7-module-slideout-wrap view="global_js_codesnippets" class="sr--tab--content" data-rel-id="sr_moscript_js" id="global_js_codesnippets">
            <sr-options-menu fourperrow>
                <sr-nav-btn data-sr-tabc="sr_snippets_methods" data-tab-target-group="gjs" class="sr--tab--call"><span><?php echo __('Methods','revslider');?></span></sr-nav-btn>
                <sr-nav-btn data-sr-tabc="sr_snippets_events" data-tab-target-group="gjs" class="sr--tab--call"><span><?php echo __('Events','revslider');?></span></sr-nav-btn>
                <sr-nav-btn data-sr-tabc="sr_snippets_examples" data-tab-target-group="gjs" class="sr--tab--call"><span><?php echo __('Examples','revslider');?></span></sr-nav-btn>
            </sr-options-menu>
            <sr7-module-slideout-content>
                <sr-wrap view="codesnippets_methods" viewchild="global_js_codesnippets" data-tab-target-group="gjs" class="sr--tab--content" id="sr_snippets_methods">
                    <sr-wrap class="sr--tab--content--wrap">
                        <?php foreach ($methods as $method) : ?>
                            <sr-wrap class="sr--with--button">
                                <sr-wrap><p title='<?php echo esc_attr(__($method['info'],'revslider'));?>'><?php echo esc_html(__($method['name'],'revslider'));?></p></sr-wrap>
                                <sr-button data-action="B.insertSnippet" clean title="<?php echo esc_attr($method['func']);?>" data-aparams='<?php echo $method['code'];?>&#013;' data-t="codes.js" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                            </sr-wrap>
                        <?php endforeach; ?>
                    </sr-wrap>
                </sr-wrap>
                <sr-wrap view="codesnippets_events" viewchild="global_js_codesnippets" data-tab-target-group="gjs" class="sr--tab--content" id="sr_snippets_events">
                    <sr-wrap class="sr--tab--content--wrap">
                        <?php foreach ($events as $title => $code) : ?>
                            <sr-wrap class="sr--with--button">
                                <sr-wrap><p><?php echo __($title,'revslider');?></p></sr-wrap>
                                <sr-button data-action="B.insertSnippet" clean data-aparams='<?php echo $code; ?>&#013;' data-t="codes.js" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                            </sr-wrap>
                        <?php endforeach; ?>
                    </sr-wrap>
                </sr-wrap>
                <sr-wrap view="codesnippets_examples" viewchild="global_js_codesnippets" data-tab-target-group="gjs" class="sr--tab--content" id="sr_snippets_examples">
                    <sr-wrap class="sr--tab--content--wrap">
                        <?php foreach ($examples as $title => $code) : ?>
                            <sr-wrap class="sr--with--button">
                                <sr-wrap><p><?php echo __($title,'revslider');?></p></sr-wrap>
                                <sr-button data-action="B.insertSnippet" clean data-aparams='<?php echo $code; ?>&#013;' data-t="codes.js" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                            </sr-wrap>
                        <?php endforeach; ?>
                    </sr-wrap>
                </sr-wrap>
            </sr7-module-slideout-content>
        </sr7-module-slideout-wrap>
        <sr-wrap view="modulescripts_css" viewchild="modulescripts" class="sr--tab--content sr--open" id="sr_moscript_css">
            <textarea name="CSS Code" codemirror="true" data-mode="css" style="height:500px" r="codes.css" viewchild="modulescripts_css"></textarea>
        </sr-wrap>
        <sr-wrap view="modulescripts_js" viewchild="modulescripts" class="sr--tab--content" id="sr_moscript_js">
           <textarea name="JavaScript Code" codemirror="true" data-mode="javascript" style="height:500px" r="codes.js" viewchild="modulescripts_js"></textarea>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>