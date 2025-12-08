<?php

namespace App;

enum CampaignStatus: string
{
    case PendingApproval = 'pending_approval';
    case Active = 'active';
    case Finished = 'finished';
}
