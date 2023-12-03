<?php

return [
    "sk" => env("STRIPE_SK"),
    "pk" => env("STRIPE_PK"),
    "webhook" => env("STRIPE_WEBHOOK_SECRET"),
];
