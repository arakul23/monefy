<?php

namespace App\Enum;

enum InvoiceStatus: string
{
    case Pending = 'Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
}
