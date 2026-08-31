<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

// Keys are rendered as kbd chips. "+" between keys is added automatically.
$tabs = [
    'sr_moshort_stage' => [
        'tab'    => __('Stage', 'revslider'),
        'icon'   => ['id' => 'Toolbar_Stage', 'w' => 18, 'h' => 18],
        'groups' => [
            [
                'title' => __('Navigation', 'revslider'),
                'items' => [
                    ['keys' => ['SPACE', __('Drag','revslider')],           'label' => __('Pan the Stage', 'revslider')],
                    ['keys' => ['CTRL / &#8984;', __('Wheel','revslider')], 'label' => __('Zoom the Stage towards the Cursor', 'revslider')],
                ]
            ],
            [
                'title' => __('Layers', 'revslider'),
                'items' => [
                    ['keys' => ['&#8592; &#8593; &#8595; &#8594;'],          'label' => __('Nudge selected Layer(s) by 1px', 'revslider')],
                    ['keys' => ['SHIFT', '&#8592; &#8593; &#8595; &#8594;'], 'label' => __('Nudge selected Layer(s) by 10px', 'revslider')],
                    ['keys' => ['DEL'],                                      'label' => __('Delete selected Layer', 'revslider')],
                    ['keys' => ['ESC'],                                      'label' => __('Deselect all Layers', 'revslider')],
                ]
            ],
            [
                'title' => __('Resize & Drag', 'revslider'),
                'items' => [
                    ['keys' => ['SHIFT', __('Resize','revslider')],          'label' => __('Toggle proportional Resizing', 'revslider')],
                    ['keys' => ['SHIFT', __('Drag','revslider')],            'label' => __('Place relative Layer next to Columns / Groups instead of into them', 'revslider')],
                    ['keys' => ['2x '.__('Click','revslider')],              'label' => __('On Resize Handle of Text / Button Layers: set Size to "auto"', 'revslider')],
                ]
            ],
        ]
    ],
    'sr_moshort_timeline' => [
        'tab'    => __('Timeline', 'revslider'),
        'icon'   => ['id' => 'Top_Bar_Timeline', 'w' => 18, 'h' => 18],
        'groups' => [
            [
                'title' => __('Navigation', 'revslider'),
                'items' => [
                    ['keys' => ['CTRL / &#8984;', __('Wheel','revslider')],  'label' => __('Zoom the Time Axis', 'revslider')],
                    ['keys' => ['SPACE', __('Drag','revslider')],            'label' => __('Pan the Timeline', 'revslider')],
                ]
            ],
            [
                'title' => __('Frames & Keyframes', 'revslider'),
                'items' => [
                    ['keys' => ['SHIFT', __('Drag Frame','revslider')],      'label' => __('Move Frame with Neighbours and matching Sub-Timeline Frames (Chars, Words, Scale, …)', 'revslider')],
                    ['keys' => ['CTRL / &#8984;', __('Drag Keyframe','revslider')], 'label' => __('Move the whole Frame instead of resizing it', 'revslider')],
                ]
            ],
        ]
    ],
    'sr_moshort_general' => [
        'tab'    => __('General', 'revslider'),
        'icon'   => ['id' => 'CheckList', 'w' => 18, 'h' => 18],
        'groups' => [
            [
                'title' => __('Slides & Lists', 'revslider'),
                'items' => [
                    ['keys' => ['2x '.__('Click','revslider')],              'label' => __('On Layer or Slide Name: rename inline', 'revslider')],
                    ['keys' => ['SHIFT', __('Add Slide','revslider')],       'label' => __('Add multiple Slides at once', 'revslider')],
                    ['keys' => ['CTRL / &#8984;', 'SPACE'],                  'label' => __('Preview current Slide in new Tab', 'revslider')],
                ]
            ],
            [
                'title' => __('Inputs & Popups', 'revslider'),
                'items' => [
                    ['keys' => ['ENTER'],                                    'label' => __('Confirm Input Value', 'revslider')],
                    ['keys' => ['ESC'],                                      'label' => __('Cancel Input / close Popup', 'revslider')],
                ]
            ],
        ]
    ],
];

//Add-Ons (e.g. DepthForge) contribute their own shortcut tabs/groups here, so their keys show up in this list when the Add-On is active. Same $tabs schema: ['tab','icon'=>['id','w','h'],'groups'=>[['title','items'=>[['keys'=>[...],'label']]]]]
$tabs = apply_filters('revslider_module_shortcuts', $tabs);

?>
<sr-modal id="sr_module_shortcuts" class="sr--no--padding sr--panel--leftsidebar" view="moduleshortcuts" style="border-radius:0px;">
    <sr-options-menu fourperrow>
        <?php $first = true; foreach ($tabs as $tid => $tab) : ?>
        <sr-nav-btn data-sr-tabc="<?php echo $tid; ?>" class="sr--tab--call<?php echo $first ? ' selected' : ''; $first = false; ?>">
            <sr-icon-wrap><svg class="sr--icon" width="<?php echo esc_attr($tab['icon']['w']); ?>" height="<?php echo esc_attr($tab['icon']['h']); ?>"><use xlink:href="#<?php echo $tab['icon']['id']; ?>"></use></svg></sr-icon-wrap>
            <span><?php echo esc_html($tab['tab']); ?></span>
        </sr-nav-btn>
        <?php endforeach; ?>
    </sr-options-menu>
    <sr-modal-content>
        <?php $first = true; foreach ($tabs as $tid => $tab) : ?>
        <sr-wrap view="module_shortcuts" viewchild="moduleshortcuts" class="sr--tab--content<?php echo $first ? ' sr--open' : ''; $first = false; ?>" id="<?php echo $tid; ?>">
            <?php foreach ($tab['groups'] as $group) : ?>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php echo esc_html($group['title']); ?></sr-separator-title>
                </sr-separator-head>
                <sr-separator-body>
                    <?php foreach ($group['items'] as $item) : ?>
                    <sr-shortcut-row>
                        <sr-shortcut-keys>
                            <?php
                                $keys = [];
                                foreach ($item['keys'] as $key) $keys[] = '<sr-kbd>'.esc_html($key).'</sr-kbd>';
                                echo implode('<span class="sr--kbd--plus">+</span>', $keys);
                            ?>
                        </sr-shortcut-keys>
                        <sr-shortcut-label><?php echo esc_html($item['label']); ?></sr-shortcut-label>
                    </sr-shortcut-row>
                    <?php endforeach; ?>
                    <sr-sp h="10"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <?php endforeach; ?>
        </sr-wrap>
        <?php endforeach; ?>
    </sr-modal-content>
</sr-modal>
