<?php
$CampManagerCore = new CampManagerCore();
$CampManagerChatGPT = new CampManagerChatGPT($CampManagerCore);
$CampManagerReceipts = new CampManagerReceipts($CampManagerCore, $CampManagerChatGPT);
$CampManagerLedger = new CampManagerLedger($CampManagerReceipts);
$CampManagerRoster = new CampManagerRoster();

$d = (new CampManagerDashboard($CampManagerLedger, $CampManagerRoster, $CampManagerReceipts))->summary();
$money = [CampManagerDashboard::class, 'money'];

$screen_id = 'camp-manager-dashboard';
$receipts_url = admin_url('admin.php?page=camp-manager-actuals');
$ledger_url = admin_url('admin.php?page=camp-manager-ledger');
$roster_url = admin_url('admin.php?page=camp-manager-members');
$can_manage_seasons = current_user_can('manage_options');

$season_settings_button = $can_manage_seasons
    ? '<button type="button" class="button" id="cm-season-settings-toggle" aria-expanded="false" aria-controls="cm-season-settings">Season settings</button>'
    : '';
?>
<style>
    .cm-dashboard-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin: 20px 0; }
    @media (max-width: 900px) { .cm-dashboard-columns { grid-template-columns: minmax(0, 1fr); } }
    .cm-dashboard .postbox { margin-bottom: 0; }
    .cm-dashboard-columns > div > .postbox { height: 100%; box-sizing: border-box; }
    .cm-dashboard > .postbox { margin-top: 20px; }
    .cm-dashboard .cm-season-settings { margin: 0 0 8px; }

    .cm-dashboard .cm-stats { margin: 6px 14px; }
    .cm-dashboard .cm-stats .cm-stat--first { padding-left: 24px; }
    .cm-callout { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; margin: 0 14px 14px; padding: 14px 16px; background: #fcf9e8; border-left: 4px solid #dba617; }
    .cm-callout strong { font-weight: 600; }

    .cm-summary { width: 100%; }
    .cm-summary th { font-weight: 400; }
    .cm-summary td { text-align: right; width: 35%; }
    .cm-summary tr.cm-total th, .cm-summary tr.cm-total td { font-weight: 600; background: #f6f7f7; border-top: 2px solid #dcdcde; }
    .cm-negative { color: #d63638; }
    .cm-postbox__footer-link { display: block; margin-top: 14px; }

    .cm-dues-table td.cm-num, .cm-dues-table th.cm-num { text-align: right; }
    .cm-dues-table tr.cm-total td { font-weight: 600; background: #f6f7f7; }
    .cm-dues-summary { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; margin: 14px 0 0; }
</style>
<div class="wrap cm-dashboard">
    <h1 class="wp-heading-inline">Camp Manager Dashboard</h1>
    <hr class="wp-header-end">
    <?php CampManagerSeason::renderSwitcher(false, $season_settings_button); ?>
    <?php if ($can_manage_seasons): ?>
        <div id="cm-season-settings" class="cm-season-settings" hidden>
            <?php CampManagerSeason::renderStartSeason(); ?>
        </div>
        <script>
            document.getElementById('cm-season-settings-toggle').addEventListener('click', function () {
                var panel = document.getElementById('cm-season-settings');
                panel.hidden = !panel.hidden;
                this.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
            });
        </script>
    <?php endif; ?>

    <?php CampManagerPostbox::boot($screen_id); ?>

    <?php CampManagerPostbox::open('dashboard-overview', 'Season overview', $screen_id); ?>
        <div class="cm-stats">
            <div class="cm-stat cm-stat--first" data-stat="paypal">
                <span class="cm-stat__label">PayPal balance</span>
                <span class="cm-stat__value"><?php echo esc_html($money($d['paypal_balance'])); ?></span>
            </div>
            <div class="cm-stat" data-stat="dues">
                <span class="cm-stat__label">Dues collected</span>
                <span class="cm-stat__value"><?php echo esc_html($money($d['camp_dues'])); ?></span>
                <span class="cm-stat__note">of <?php echo esc_html($money($d['dues_expected'])); ?> expected</span>
            </div>
            <div class="cm-stat" data-stat="members">
                <span class="cm-stat__label">Members</span>
                <span class="cm-stat__value"><?php echo (int) $d['members']; ?></span>
                <span class="cm-stat__note"><?php echo (int) $d['members_paid']; ?> fully paid</span>
            </div>
            <div class="cm-stat" data-stat="receipts">
                <span class="cm-stat__label">Unpaid receipts</span>
                <span class="cm-stat__value"><?php echo esc_html($money($d['unpaid_receipts'])); ?></span>
                <a class="cm-stat__note" href="<?php echo esc_url($receipts_url); ?>">Review receipts</a>
            </div>
        </div>
        <?php if ($d['unpaid_receipts'] > 0): ?>
            <div class="cm-callout" data-callout="receipts">
                <strong><?php echo esc_html($money($d['unpaid_receipts'])); ?> in receipts awaiting reimbursement.</strong>
                <a href="<?php echo esc_url($receipts_url); ?>">View receipts</a>
            </div>
        <?php endif; ?>
    <?php CampManagerPostbox::close(); ?>

    <div class="cm-dashboard-columns">
        <div>
            <?php CampManagerPostbox::open('dashboard-financial', 'Financial summary', $screen_id, ['padded' => true]); ?>
                <table class="widefat striped cm-summary">
                    <tbody>
                        <tr><th>Starting funds</th><td><?php echo esc_html($money($d['starting_balance'])); ?></td></tr>
                        <tr><th>Camp dues collected</th><td><?php echo esc_html($money($d['camp_dues'])); ?></td></tr>
                        <tr><th>Donations collected</th><td><?php echo esc_html($money($d['donations'])); ?></td></tr>
                        <tr><th>Other revenue</th><td><?php echo esc_html($money($d['other_revenue'])); ?></td></tr>
                        <tr class="cm-total"><th>Total revenue</th><td><?php echo esc_html($money($d['money_in'])); ?></td></tr>
                        <tr><th>Revenue spent</th><td><?php echo esc_html($money($d['money_out'])); ?></td></tr>
                        <tr class="cm-total"><th>Funds remaining</th><td><?php echo esc_html($money($d['funds_remaining'])); ?></td></tr>
                    </tbody>
                </table>
                <a class="cm-postbox__footer-link" href="<?php echo esc_url($ledger_url); ?>">View financial details</a>
            <?php CampManagerPostbox::close(); ?>
        </div>
        <div>
            <?php CampManagerPostbox::open('dashboard-ledger', 'Ledger summary', $screen_id, ['padded' => true]); ?>
                <table class="widefat striped cm-summary">
                    <tbody>
                        <tr><th>Money in</th><td><?php echo esc_html($money($d['money_in'])); ?></td></tr>
                        <tr><th>Money out</th><td><?php echo esc_html($money($d['money_out'])); ?></td></tr>
                        <tr><th>Net revenue</th><td><?php echo esc_html($money($d['funds_remaining'])); ?></td></tr>
                        <tr><th>Unpaid receipts</th><td><?php echo esc_html($money($d['unpaid_receipts'])); ?></td></tr>
                        <tr class="cm-total"><th>Remaining</th><td class="<?php echo $d['ledger_remaining'] < 0 ? 'cm-negative' : ''; ?>"><?php echo esc_html($money($d['ledger_remaining'])); ?></td></tr>
                    </tbody>
                </table>
                <a class="cm-postbox__footer-link" href="<?php echo esc_url($ledger_url); ?>">View ledger</a>
            <?php CampManagerPostbox::close(); ?>
        </div>
    </div>

    <?php
    CampManagerPostbox::open(
        'dashboard-membership',
        'Membership & dues',
        $screen_id,
        ['padded' => true, 'header' => '<a href="' . esc_url($roster_url) . '">View roster</a>']
    );
    ?>
        <p class="cm-dues-lead" style="margin-top: 0;">
            <?php echo (int) $d['members']; ?> members &middot;
            <?php echo (int) $d['members_paid']; ?> paid &middot;
            <?php echo esc_html($money($d['dues_remaining'])); ?> dues remaining
        </p>
        <table class="widefat striped cm-dues-table">
            <thead>
                <tr>
                    <th>Dues type</th>
                    <th class="cm-num">Members</th>
                    <th class="cm-num">Paid</th>
                    <th class="cm-num">Expected revenue</th>
                </tr>
            </thead>
            <tbody>
                <tr data-dues="full">
                    <td>Full camp dues ($<?php echo (int) CampManagerDashboard::FULL_DUES; ?>)</td>
                    <td class="cm-num"><?php echo (int) $d['regular_members']; ?></td>
                    <td class="cm-num"><?php echo (int) $d['regular_paid']; ?></td>
                    <td class="cm-num"><?php echo esc_html($money($d['regular_expected'])); ?></td>
                </tr>
                <tr data-dues="low">
                    <td>Low-income dues ($<?php echo (int) CampManagerDashboard::LOW_INCOME_DUES; ?>)</td>
                    <td class="cm-num"><?php echo (int) $d['low_members']; ?></td>
                    <td class="cm-num"><?php echo (int) $d['low_paid']; ?></td>
                    <td class="cm-num"><?php echo esc_html($money($d['low_expected'])); ?></td>
                </tr>
                <tr class="cm-total" data-dues="total">
                    <td>Total</td>
                    <td class="cm-num"><?php echo (int) $d['members']; ?></td>
                    <td class="cm-num"><?php echo (int) ($d['regular_paid'] + $d['low_paid']); ?></td>
                    <td class="cm-num"><?php echo esc_html($money($d['dues_expected'])); ?></td>
                </tr>
            </tbody>
        </table>
        <p class="cm-dues-summary">
            <span>Collected revenue: <strong><?php echo esc_html($money($d['money_in'])); ?></strong></span>
            <span>Estimated revenue remaining: <strong><?php echo esc_html($money($d['estimated_revenue_remaining'])); ?></strong></span>
        </p>
    <?php CampManagerPostbox::close(); ?>
</div>
