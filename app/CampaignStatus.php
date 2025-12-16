<?php

namespace App;

enum CampaignStatus: string
{
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case FINISHED = 'finished';
    case REJECTED = 'rejected';
}
