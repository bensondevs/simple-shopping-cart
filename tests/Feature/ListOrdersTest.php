<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Livewire\ListOrders;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication_to_view_orders_page(): void
    {
        $this->get(route('orders'))
            ->assertRedirect(route('login'));
    }

    public function test_displays_empty_state_when_user_has_no_orders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListOrders::class)
            ->assertSee(__('You have no orders yet'))
            ->assertSee(__('My Orders'));
    }

    public function test_displays_orders_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10.00]); // $10.00

        $order = new Order();
        $order->user()->associate($user);
        $order->status = OrderStatus::Pending;
        $order->reference = 'ORD-20250101120000';
        $order->total = 10.00;
        $order->save();

        $orderProduct = new OrderProduct();
        $orderProduct->order()->associate($order);
        $orderProduct->product()->associate($product);
        $orderProduct->name = $product->name;
        $orderProduct->quantity = 1;
        $orderProduct->price = 10.00;
        $orderProduct->save();

        $this->actingAs($user);

        Livewire::test(ListOrders::class)
            ->assertSee('ORD-20250101120000')
            ->assertSee('Pending')
            ->assertSee('10.00')
            ->assertSee('1 item(s)');
    }

    public function test_does_not_display_orders_from_other_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        // Create order for user1
        $order1 = new Order();
        $order1->user()->associate($user1);
        $order1->status = OrderStatus::Pending;
        $order1->reference = 'ORD-USER1';
        $order1->total = 10.00;
        $order1->save();

        // Create order for user2
        $order2 = new Order();
        $order2->user()->associate($user2);
        $order2->status = OrderStatus::Pending;
        $order2->reference = 'ORD-USER2';
        $order2->total = 20.00;
        $order2->save();

        $this->actingAs($user1);

        Livewire::test(ListOrders::class)
            ->assertSee('ORD-USER1')
            ->assertDontSee('ORD-USER2');
    }

    public function test_can_show_order_details(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 15.00]); // $15.00

        $order = new Order();
        $order->user()->associate($user);
        $order->status = OrderStatus::Pending;
        $order->reference = 'ORD-TEST';
        $order->total = 30.00; // 15.00 * 2
        $order->save();

        $orderProduct = new OrderProduct();
        $orderProduct->order()->associate($order);
        $orderProduct->product()->associate($product);
        $orderProduct->name = $product->name;
        $orderProduct->quantity = 2;
        $orderProduct->price = 15.00;
        $orderProduct->save();

        $this->actingAs($user);

        Livewire::test(ListOrders::class)
            ->call('showOrderDetails', $order->id)
            ->assertSet('selectedOrder.id', $order->id)
            ->assertSee('ORD-TEST')
            ->assertSee($product->name)
            ->assertSee('15.00')
            ->assertSee('2');
    }

    public function test_can_close_order_details(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = new Order();
        $order->user()->associate($user);
        $order->status = OrderStatus::Pending;
        $order->reference = 'ORD-TEST';
        $order->total = 10.00;
        $order->save();

        $this->actingAs($user);

        Livewire::test(ListOrders::class)
            ->call('showOrderDetails', $order->id)
            ->assertSet('selectedOrder', fn ($order) => $order !== null)
            ->call('closeOrderDetails')
            ->assertSet('selectedOrder', null);
    }

    public function test_cannot_view_order_details_from_other_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        $order = new Order();
        $order->user()->associate($user2);
        $order->status = OrderStatus::Pending;
        $order->reference = 'ORD-OTHER';
        $order->total = 10.00;
        $order->save();

        $this->actingAs($user1);

        Livewire::test(ListOrders::class)
            ->call('showOrderDetails', $order->id)
            ->assertSet('selectedOrder', null);
    }

    public function test_displays_orders_in_latest_first_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Create older order
        $order1 = new Order();
        $order1->user()->associate($user);
        $order1->status = OrderStatus::Pending;
        $order1->reference = 'ORD-OLD';
        $order1->total = 10.00;
        $order1->created_at = now()->subDay();
        $order1->save();

        // Create newer order
        $order2 = new Order();
        $order2->user()->associate($user);
        $order2->status = OrderStatus::Pending;
        $order2->reference = 'ORD-NEW';
        $order2->total = 20.00;
        $order2->created_at = now();
        $order2->save();

        $this->actingAs($user);

        Livewire::test(ListOrders::class)
            ->assertSeeInOrder(['ORD-NEW', 'ORD-OLD']);
    }
}
