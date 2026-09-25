<?php

/** The figures on the Camp Manager dashboard, for the season being viewed. */
class CampManagerDashboard
{
    const FULL_DUES = 350;
    const LOW_INCOME_DUES = 250;

    private $ledger;
    private $roster;
    private $receipts;

    public function __construct(CampManagerLedger $ledger, CampManagerRoster $roster, CampManagerReceipts $receipts)
    {
        $this->ledger = $ledger;
        $this->roster = $roster;
        $this->receipts = $receipts;
    }

    public function summary(): array
    {
        $starting_balance = (float) $this->ledger->startingBalance();
        $money_in = (float) $this->ledger->totalMoneyIn();
        $money_out = (float) $this->ledger->totalMoneyOut();
        $unpaid_receipts = (float) $this->receipts->totalUnpaidReceipts();
        $dues_collected = (float) $this->ledger->totalCampDues();

        // Dues are worked out over confirmed members, at the full or the low-income rate.
        $members = (int) $this->roster->countConfirmedRosterMembers();
        $low_members = (int) $this->roster->countLowIncomeMembers();
        $regular_members = $members - $low_members;
        $paid_low = (int) $this->roster->countPaidLowIncomeCampDues();
        $paid_all = (int) $this->roster->countPaidCampDues();
        $paid_regular = $paid_all - $paid_low;
        $regular_expected = $regular_members * self::FULL_DUES;
        $low_expected = $low_members * self::LOW_INCOME_DUES;
        $dues_remaining = (float) $this->roster->totalUnpaidCampDues();

        return [
            'starting_balance'  => $starting_balance,
            'money_in'          => $money_in,
            'money_out'         => $money_out,
            'paypal_balance'    => $starting_balance + $money_in - $money_out,
            'funds_remaining'   => $money_in - $money_out,
            'camp_dues'         => $dues_collected,
            'donations'         => (float) $this->ledger->totalDonations(),
            'other_revenue'     => (float) $this->ledger->totalAssetsSold(),
            'unpaid_receipts'   => $unpaid_receipts,
            'ledger_remaining'  => $money_in - $money_out - $unpaid_receipts,

            'members'           => $members,
            'members_paid'      => $paid_all,
            'regular_members'   => $regular_members,
            'regular_paid'      => $paid_regular,
            'regular_expected'  => (float) $regular_expected,
            'low_members'       => $low_members,
            'low_paid'          => $paid_low,
            'low_expected'      => (float) $low_expected,
            'dues_expected'     => (float) ($regular_expected + $low_expected),
            'dues_remaining'    => $dues_remaining,
            'estimated_revenue_remaining' => $money_in + $dues_remaining - $money_out,
        ];
    }

    /** "$1,234.50", or "-$350.00" for a negative amount. */
    public static function money(float $amount): string
    {
        return ($amount < 0 ? '-' : '') . '$' . number_format(abs($amount), 2);
    }
}
