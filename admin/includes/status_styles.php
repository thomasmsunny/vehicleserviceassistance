<!-- 
=======================================================================
UNIFIED STATUS STYLING FOR ALL PAGES
=======================================================================
This file provides consistent status badge styling across:
- Admin pages (dashboard, bookings, services, drivers)
- Driver pages (assigned pickups, working)
- Customer pages

Include this file in the <head> section of any page displaying status
=======================================================================
-->

<style>
    /* ========================================
       UNIVERSAL STATUS BADGE BASE STYLES
       ======================================== */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75em;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    
    /* ========================================
       BOOKING STATUS COLORS (All Variations)
       ======================================== */
    
    /* PENDING - Yellow/Amber */
    .status-Pending,
    .status-pending,
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }
    
    /* QUOTED - Yellow/Amber */
    .status-Quoted,
    .status-quoted,
    .status-badge.quoted {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }
    
    /* PAY NOW / PAYMENT PENDING - Red */
    .status-PayNow,
    .status-paynow,
    .status-PaymentPending,
    .status-paymentpending,
    .status-pay-now,
    .status-badge.pay-now {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #dc3545;
    }
    
    /* PAID / PAYMENT DONE - Purple */
    .status-PaymentDone,
    .status-paymentdone,
    .status-payment-done,
    .status-PayDone,
    .status-paydone,
    .status-pay-done,
    .status-Paid,
    .status-paid,
    .status-badge.pay-done,
    .status-badge.payment-done {
        background: #e2d9f3;
        color: #4a148c;
        border: 1px solid #9c27b0;
    }
    
    /* IN PROGRESS / PROCESSING - Blue/Cyan */
    .status-InProgress,
    .status-inprogress,
    .status-Processing,
    .status-processing,
    .status-in-progress,
    .status-badge.in-progress,
    .status-badge.assigned,
    .status-badge.on-route {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #17a2b8;
    }
    
    /* PICKED UP - Blue */
    .status-PickedUp,
    .status-pickedup,
    .status-picked-up,
    .status-badge.picked-up {
        background: #cfe2ff;
        color: #084298;
        border: 1px solid #0d6efd;
    }
    
    /* COMPLETED / COMPLETE - Green */
    .status-Completed,
    .status-completed,
    .status-Complete,
    .status-complete,
    .status-badge.complete,
    .status-badge.finalized {
        background: #d4edda;
        color: #155724;
        border: 1px solid #28a745;
    }
    
    /* DELIVERED - Green */
    .status-Delivered,
    .status-delivered,
    .status-badge.delivered {
        background: #d4edda;
        color: #155724;
        border: 1px solid #28a745;
    }
    
    /* CANCELLED / CANCELED - Red */
    .status-Cancelled,
    .status-cancelled,
    .status-Canceled,
    .status-canceled,
    .status-badge.cancelled,
    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #dc3545;
    }
    
    /* REJECTED - Red */
    .status-Rejected,
    .status-rejected,
    .status-badge.rejected {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #dc3545;
    }
    
    /* ========================================
       SERVICE STATUS COLORS
       ======================================== */
    
    /* ACTIVE - Green (Bootstrap badge-success compatible) */
    .status-active,
    .status-Active,
    .badge-success {
        background: #d4edda !important;
        color: #155724 !important;
        border: 1px solid #28a745;
    }
    
    /* INACTIVE - Gray (Bootstrap badge-secondary compatible) */
    .status-inactive,
    .status-Inactive,
    .badge-secondary {
        background: #e2e3e5 !important;
        color: #383d41 !important;
        border: 1px solid #6c757d;
    }
    
    /* ========================================
       DRIVER STATUS COLORS
       ======================================== */
    
    /* AVAILABLE - Green */
    .status-Available,
    .status-available {
        background: #d4edda;
        color: #155724;
        border: 1px solid #28a745;
    }
    
    /* UNAVAILABLE / BUSY / OFFLINE - Orange/Warning */
    .status-Unavailable,
    .status-unavailable,
    .status-Busy,
    .status-busy,
    .status-Offline,
    .status-offline {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }
    
    /* ========================================
       BOOTSTRAP BADGE COMPATIBILITY
       ======================================== */
    
    /* Bootstrap warning badge */
    .badge-warning {
        background: #fff3cd !important;
        color: #856404 !important;
        border: 1px solid #ffc107;
    }
    
    /* Bootstrap danger badge */
    .badge-danger {
        background: #f8d7da !important;
        color: #721c24 !important;
        border: 1px solid #dc3545;
    }
    
    /* Bootstrap info badge */
    .badge-info {
        background: #d1ecf1 !important;
        color: #0c5460 !important;
        border: 1px solid #17a2b8;
    }
    
    /* ========================================
       DEFAULT FALLBACK FOR UNKNOWN STATUS
       ======================================== */
    .status-badge:not([class*="status-"]):not([class*="badge-"]) {
        background: #e9ecef;
        color: #495057;
        border: 1px solid #dee2e6;
    }
    
    /* ========================================
       HOVER EFFECTS
       ======================================== */
    .status-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* ========================================
       ICON SPACING IN BADGES
       ======================================== */
    .status-badge i,
    .badge i {
        margin-right: 4px;
    }
</style>
