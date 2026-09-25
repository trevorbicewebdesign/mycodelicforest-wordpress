<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerRolesTable extends WP_List_Table
{
    private $roles;

    public function __construct(CampManagerRoles $roles)
    {
        $this->roles = $roles;

        parent::__construct([
            'singular' => 'Camp Role',
            'plural'   => 'Camp Roles',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'          => '<input type="checkbox" />', // For bulk actions
            'sort_order'  => 'Order',
            'name'        => 'Role',
            'members'     => 'Held By',
            'permissions' => 'Camp Manager Access',
            'description' => 'Description',
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
                $url = admin_url('admin.php?page=camp-manager-add-role&id=' . urlencode($item['id']));
                $html = str_repeat('<span aria-hidden="true">— </span>', (int) $item['depth'])
                    . sprintf('<a href="%s"><strong>%s</strong></a>', esc_url($url), esc_html($item['name']));
                if (!empty($item['is_circle'])) {
                    $html .= ' <span class="description">(circle)</span>';
                }
                if (!empty($item['also_known_as'])) {
                    $html .= '<br><small>also known as ' . esc_html(implode(', ', array_map(function ($name, $seasons) {
                        return $name . ' (' . implode(', ', $seasons) . ')';
                    }, array_keys($item['also_known_as']), $item['also_known_as']))) . '</small>';
                }
                return $html;
            case 'members':
                $names = array_map(function ($member) {
                    return esc_html(trim($member['fname'] . ' ' . $member['lname']) . (!empty($member['playaname']) ? " ({$member['playaname']})" : ''));
                }, $item['members']);
                // A circle needs no holder of its own: its roles are held.
                return $names ? implode('<br>', $names) : (!empty($item['is_circle']) ? '' : '<em>Unfilled</em>');
            case 'permissions':
                return esc_html(implode(', ', array_map(function ($area) {
                    return CampManagerRoles::AREAS[$area];
                }, $item['permissions'])));
            case 'description':
                return esc_html(wp_trim_words(wp_strip_all_tags($item['description']), 20));
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action() && !empty($_POST['camp-roles']) && is_array($_POST['camp-roles'])) {
            check_admin_referer('bulk-' . $this->_args['plural']);
            $this->roles->deleteRoles($_POST['camp-roles']);
        }
    }

    public function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
        ];
    }

    public function prepare_items()
    {
        $this->items = $this->roles->getRoles(CampManagerSeason::selected());
        $this->_column_headers = [$this->get_columns(), [], []];
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="camp-roles[]" value="%s" />', esc_attr($item['id']));
    }
}
