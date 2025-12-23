<?php

namespace App\Events;

/**
 * Deprecated event placeholder.
 *
 * Broadcasting has been removed from this project by design. This class remains as a no-op
 * placeholder to avoid accidental breakage if referenced elsewhere. Do not rely on broadcasting;
 * order status updates are driven solely by webhook callbacks and database state.
 */
class OrderStatusUpdated
{
    public $orderId;
    public $status;
    public $message;
    public $trxid;

    public function __construct($order, $digiflazzStatus = null)
    {
        $this->orderId = $order->id;
        $this->status = $order->status;
        $this->message = $digiflazzStatus->message ?? ($digiflazzStatus->additional_data['message'] ?? null) ?? null;
        $this->trxid = $digiflazzStatus->trxid ?? null;
    }
}
