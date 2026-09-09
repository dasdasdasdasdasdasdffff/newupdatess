<?php
/**
 * CapitalNest Nepal - Enterprise Formatter Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Formatter {
    public static function currency(float|string|int $amount, string $symbol = 'रू'): string {
        $num = (float)$amount;
        return $symbol . ' ' . number_format($num, 2, '.', ',');
    }

    public static function date(?string $dateStr, string $format = 'M d, Y h:i A'): string {
        if (!$dateStr) return 'N/A';
        try {
            $date = new \DateTime($dateStr);
            return $date->format($format);
        } catch (\Exception $e) {
            return $dateStr;
        }
    }

    public static function dateTime(?string $dateStr, string $format = 'M d, Y h:i A'): string {
        return self::date($dateStr, $format);
    }

    public static function relativeTime(?string $dateStr): string {
        if (!$dateStr) return 'N/A';
        try {
            $timestamp = strtotime($dateStr);
            $diff = time() - $timestamp;
            if ($diff < 60) return 'Just now';
            if ($diff < 3600) return floor($diff / 60) . ' mins ago';
            if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
            if ($diff < 604800) return floor($diff / 86400) . ' days ago';
            return date('M d, Y', $timestamp);
        } catch (\Exception $e) {
            return $dateStr;
        }
    }

    public static function statusBadge(string $status): string {
        $status = strtolower(trim($status));
        $classes = match ($status) {
            'active', 'approved', 'verified', 'completed', 'successful', 'rewarded', 'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'pending', 'processing', 'open' => 'bg-amber-50 text-amber-700 border-amber-200',
            'rejected', 'cancelled', 'failed', 'suspended', 'closed' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-neutral-100 text-neutral-700 border-neutral-200'
        };
        $label = strtoupper(str_replace('_', ' ', $status));
        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border %s">%s</span>',
            $classes,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    public static function kycBadge(?string $status): string {
        $status = strtolower(trim((string)($status ?? 'not_submitted')));

        $classes = match ($status) {
            'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'not_submitted', 'not-submitted', 'none', 'missing' => 'bg-neutral-100 text-neutral-700 border-neutral-200',
            default => 'bg-sky-50 text-sky-700 border-sky-200'
        };

        $label = match ($status) {
            'verified' => 'Verified',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
            'not_submitted', 'not-submitted', 'none', 'missing' => 'Not Submitted',
            default => ucfirst(str_replace('_', ' ', $status))
        };

        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold border %s">%s</span>',
            $classes,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    public static function transactionTypeBadge(string $type): string {
        $type = strtolower(trim($type));

        $classes = match ($type) {
            'deposit', 'credit', 'profit', 'referral' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'withdrawal', 'debit', 'investment' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'adjustment' => 'bg-sky-50 text-sky-700 border border-sky-200',
            default => 'bg-neutral-100 text-neutral-700 border border-neutral-200'
        };

        $label = strtoupper(str_replace('_', ' ', $type));

        return sprintf(
            '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold %s">%s</span>',
            $classes,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }
}
