<?php

namespace Botble\Logistics\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Tables\Formatters\PriceFormatter;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderShippingTable extends TableAbstract
{
    public function setup(): void
    {
        $status = request('status');
        $actions = match ($status) {
            'DRAFT' => [
                EditAction::make()
                    ->route('logistics.shipping.order.create')
                    ->label('Tạo đơn')
                    ->icon('ti ti-plus'),
            ],

            null => [
                EditAction::make()
                    ->label('...')
                    ->icon('ti ti-eye'),
            ],

            default => [
                EditAction::make()
                    ->route('logistics.shipping.order.edit')
                    ->label('Chi tiết')
                    ->icon('ti ti-eye'),
            ],
        };
        

        $this
            ->model(Order::class)
            ->addActions($actions);
        // $this->model(Order::class);
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('payment_status', function (Order $item) {
                if (! is_plugin_active('payment')) {
                    return '&mdash;';
                }

                return $item->payment->status->label() ? BaseHelper::clean(
                    $item->payment->status->toHtml()
                ) : '&mdash;';
            })
            ->editColumn('payment_method', function (Order $item) {
                if (! is_plugin_active('payment')) {
                    return '&mdash;';
                }

                return BaseHelper::clean($item->payment->payment_channel->displayName() ?: '&mdash;');
            })
            ->formatColumn('amount', PriceFormatter::class)
            ->editColumn('shipping_amount', function (Order $item) {
                return $item->shipment->exists() ? $item->shipping_amount : '&mdash;';
            })
            ->editColumn('shipment_status', function (Order $item) {
                $status = $item->shipment->status?->getValue();
                return match ($status) {
                    'not_approved' => '<span class="badge bg-warning text-white">Chưa phê duyệt</span>',
                    'approved' => '<span class="badge bg-info text-white">Đã phê duyệt</span>',
                    'pending' => '<span class="badge bg-warning text-white">Đang chờ</span>',
                    'arrange_shipment' => '<span class="badge bg-secondary text-white">Sắp xếp giao hàng</span>',
                    'ready_to_be_shipped_out' => '<span class="badge bg-dark text-white">Sẵn sàng giao</span>',
                    'picking' => '<span class="badge bg-info text-white">Đang lấy hàng</span>',
                    'delay_picking' => '<span class="badge bg-warning text-white">Trễ lấy hàng</span>',
                    'picked' => '<span class="badge bg-primary text-white">Đã lấy hàng</span>',
                    'not_picked' => '<span class="badge bg-secondary text-white">Chưa lấy hàng</span>',
                    'delivering' => '<span class="badge bg-info text-white">Đang giao</span>',
                    'delivered' => '<span class="badge bg-success text-white">Đã giao</span>',
                    'not_delivered' => '<span class="badge bg-secondary text-white">Chưa giao</span>',
                    'audited' => '<span class="badge bg-dark text-white">Đã kiểm toán</span>',
                    'canceled' => '<span class="badge bg-danger text-white">Đã hủy</span>',
                    default => '<span class="badge bg-secondary text-white">+</span>',
                };
            });
            // ->addColumn('create_shipping', function ($item) {
            //     $url = route('logistics.shipping.order.create', $item->id);
            //     return '<a href="' . $url . '" class="btn btn-sm btn-primary">
            //                 <i class="ti ti-plus"></i> Tạo đơn
            //             </a>';
            // });

        if (EcommerceHelper::isTaxEnabled()) {
            $data = $data->formatColumn('tax_amount', PriceFormatter::class);
        }

        $data = $data
            ->filter(function ($query) {
                return $this->filterOrders($query);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $with = ['user', 'shipment', 'address'];

        if (is_plugin_active('payment')) {
            $with[] = 'payment';
        }

        $query = $this
            ->getModel()
            ->query()
            ->with($with)
            ->select([
                'id',
                'status',
                'user_id',
                'created_at',
                'amount',
                'tax_amount',
                'shipping_amount',
                'payment_id',
            ])
            ->where('is_finished', 1);  
            
            $status = request('status');

            $map = [
                'DRAFT' => ['not_approved', 'approved','pending','arrange_shipment'],
                'CREATED' => ['ready_to_be_shipped_out', 'picking','delay_picking','not_picked'],
                'SHIPPING' => ['picked', 'delivering'],
                'DELIVERED' => ['delivered', 'audited'],
                'CANCELLED' => ['not_delivered', 'canceled'],
            ];

            if ($status) {
                $query->whereHas('shipment', function ($q) use ($status, $map) {

                    if (isset($map[$status])) {
                        $q->whereIn('status', $map[$status]);
                    } else {
                        $q->where('status', $status);
                    }

                });
            }

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        $columns = [
            IdColumn::make(),
            FormattedColumn::make('user_id')
                ->title(trans('plugins/ecommerce::order.email'))
                ->alignStart()
                ->orderable(false)
                ->renderUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();

                    return sprintf(
                        '%s <br> %s <br> %s',
                        $item->user->name ?: $item->address->name,
                        Html::mailto($item->user->email ?: $item->address->email, obfuscate: false),
                        $item->user->phone ?: $item->address->phone
                    );
                })
                ->responsivePriority(99),
            Column::formatted('amount')
                ->title(trans('plugins/ecommerce::order.amount')),
        ];

        if (is_plugin_active('payment')) {
            $columns = array_merge($columns, [
                Column::make('payment_status')
                    ->name('payment_id')
                    ->title(trans('plugins/ecommerce::order.payment_status_label')),
            ]);
            
        }
        $columns[] = Column::make('shipment_status')
        ->title('Trạng thái vận chuyển');
        $columns[] = StatusColumn::make()->alignStart();

        // $columns[] = Column::make('create_shipping')
        // ->title('Tạo đơn');

        return array_merge($columns, [
            CreatedAtColumn::make(),
        ]);
    }
}
