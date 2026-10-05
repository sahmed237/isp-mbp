<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaceholderController extends Controller
{
    public function show(string $module): View
    {
        $modules = [
            'invoices' => [
                'title' => 'Invoices & Billing Automation',
                'phase' => 'Phase 3 — Billing Engine & Payment Gateways',
                'desc' => 'Automated invoice generation, prorated calculations, tax rules, and PDF invoices.',
                'features' => ['Recurring Billing Schedule', 'Prorated Invoicing', 'PDF Generation & Email Delivery', 'Overdue Penalties & Grace Periods'],
            ],
            'payments' => [
                'title' => 'Payment Reconciliation & Gateways',
                'phase' => 'Phase 3 — Billing Engine & Payment Gateways',
                'desc' => 'Integration with Nigerian payment gateways (Paystack, Flutterwave, Moniepoint) and bank transfer reconciliation.',
                'features' => ['Paystack Inline & Webhooks', 'Moniepoint Virtual Accounts', 'Manual Bank Wire Matching', 'Automated Receipts & SMS Alert'],
            ],
            'radius' => [
                'title' => 'AAA FreeRADIUS Engine',
                'phase' => 'Phase 4 — RADIUS Infrastructure Foundation',
                'desc' => 'Direct FreeRADIUS / DaloRADIUS integration, NAS client management, radcheck, radreply, and radacct accounting streams.',
                'features' => ['RADIUS User Provisioning', 'Dynamic MikroTik Rate-Limit Attributes', 'Real-time Session Accounting', 'Simultaneous Use Restriction'],
            ],
            'mikrotik-api' => [
                'title' => 'MikroTik RouterOS API Automation',
                'phase' => 'Phase 5 — RouterOS REST & API Provisioning',
                'desc' => 'Direct RouterOS v6 / v7 REST communication for PPPoE secrets, Hotspot user profiles, dynamic queue enforcement, and pool routing.',
                'features' => ['Dynamic Simple Queues & PCQ', 'PPPoE Client Provisioning', 'Active Session Disconnect / CoA', 'Hotspot Captive Portal Sync'],
            ],
            'tickets' => [
                'title' => 'Customer Support & Ticketing Desk',
                'phase' => 'Phase 2 & 9 — Support & Self-Service',
                'desc' => 'SLA tracking, departmental escalation, network fault correlation, and customer communication history.',
                'features' => ['Helpdesk Ticketing', 'Departmental Routing', 'Customer Portal Ticket Logging', 'Internal Staff Notes'],
            ],
            'reports' => [
                'title' => 'Executive & Financial Reports',
                'phase' => 'Phase 8 — Monitoring & Network Analytics',
                'desc' => 'ARPU (Average Revenue Per User), MRR (Monthly Recurring Revenue), churn rates, and bandwidth utilization analytics.',
                'features' => ['Revenue Breakdown by Branch', 'Customer Churn Analysis', 'Bandwidth Peak Utilization', 'Export to Excel & PDF'],
            ],
        ];

        $info = $modules[$module] ?? [
            'title' => ucfirst($module) . ' Module',
            'phase' => 'Future Development Roadmap',
            'desc' => 'This module is scheduled in our phase-based development architecture.',
            'features' => ['Architecture Foundation Ready', 'Database Schema Prepared'],
        ];

        return view('admin.placeholder', compact('info'));
    }
}
