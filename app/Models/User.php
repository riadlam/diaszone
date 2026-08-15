<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_avatar',
        'is_admin',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && $this->isActive();
    }

    public function hasUnlimitedWheelSpins(): bool
    {
        return $this->isAdmin() && $this->isActive();
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function digiflazzStatuses(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(DigiflazzStatus::class, Order::class);
    }

    public function item4gamerOrders(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Item4GamerOrder::class, Order::class);
    }

    /**
     * Top-ups the providers actually delivered to this customer, so multi-quantity
     * orders and orders still marked as sending are counted correctly.
     */
    public function deliveredTopupsCount(): int
    {
        $digiflazz = $this->digiflazzStatuses()
            ->where(function ($query) {
                $query->whereRaw("LOWER(digiflazz_statuses.status) = 'sukses'")
                    ->orWhere('digiflazz_statuses.rc', '00');
            })
            ->count();

        $item4gamer = $this->item4gamerOrders()
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(item4gamer_orders.status)'), ['completed', 'success'])
            ->sum('item4gamer_orders.quantity');

        return (int) $digiflazz + (int) $item4gamer;
    }

    public function orderedItemsQuantity(): int
    {
        return (int) OrderItem::query()
            ->whereIn('order_id', $this->orders()->select('orders.id'))
            ->sum('quantity');
    }

    public function lifetimeSpendDzd(): float
    {
        return (float) $this->orders()
            ->where('status', 'completed')
            ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(final_price, original_price, 0)'));
    }

    public function wheelClaims(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WheelClaim::class);
    }

    public function wheelSpinLedgers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WheelSpinLedger::class);
    }

    public function wheelProgress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WheelUserProgress::class);
    }
}
