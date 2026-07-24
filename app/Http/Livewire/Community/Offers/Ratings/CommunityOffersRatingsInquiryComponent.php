<?php

namespace App\Http\Livewire\Community\Offers\Ratings;

use App\Models\Offers\Ratings\OfferRatings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;

class CommunityOffersRatingsInquiryComponent extends LivewireDatatable
{
    use LivewireAlert;


    /**
     * set variables
     */
    public $exportable = true;
    public $hideable = 'select';
    public $afterTableSlot = 'modals.community.posts.edit';
    public $model = OfferRatings::class;
    public array $rating;
    public bool $showDeleteModal = false;
    public bool $showEditModal = false;
    public bool $showRestoreModal = false;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public array $restoreModalTexts;
    public string $page_type = 'all';
    public bool $has_delete = false;
    public ?int $restore = null;

    /**
     * @var array
     */
    public $listeners = ['rerenderDataTable' => 'changeType'];

    /**
     * @param $params
     */
    public function changeType($params)
    {
        $this->page_type = $params['page_type'];
    }

    /**
     * set columns to render
     * @return array
     */
    public function columns(): array
    {
        return [
            /*Column::checkbox(),*/
            NumberColumn::name('id')
                ->label('#')
                ->filterable()
                ->searchable(),
            NumberColumn::name('offer_id')
                ->label(__('pages/community/offers/ratings/index.content.datatable.offer_id'))
                ->filterable()
                ->searchable()
                ->linkTo('admin/community/offers'),
            Column::callback(['id', 'updated_at'], function ($id) {
                $user_type = OfferRatings::findOrFail($id)
                    ->user->user_type;
                return ucwords($user_type);
            })
                ->label(__('pages/community/offers/ratings/index.content.datatable.user_type'))
                ->searchable()
                ->unsortable(),
            NumberColumn::name('user_id')
                ->label(__('pages/community/offers/ratings/index.content.datatable.user_id'))
                ->filterable()
                ->searchable(),
            Column::callback(['id'], function ($id) {
                $username = OfferRatings::findOrFail($id)
                    ->user->name;
                return ucwords($username);
            })
                ->label(__('pages/community/offers/ratings/index.content.datatable.user_name'))
                ->searchable(),
            Column::callback('comment', function ($comment) {
                return Str::limit($comment, 30);
            })
                ->label(__('pages/community/offers/ratings/index.content.datatable.comment'))
                ->filterable()
                ->searchable(),
            Column::name('status')
                ->label(__('pages/community/offers/ratings/index.content.datatable.status'))
                ->filterable(['Approved', 'Pending', 'Unapproved'])
                ->searchable(),
            NumberColumn::name('rate')
                ->label(__('pages/community/offers/ratings/index.content.datatable.rate'))
                ->filterable()
                ->searchable(),
            DateColumn::name('created_at')
                ->label(__('datatable.created_at'))
                ->filterable()
                ->searchable()
                ->hide(),
//            Column::callback(['id', 'created_at'], function ($id) {
//                return view('admin.pages.community.offers.ratings.table-actions', ['id' => $id]);
//            })
//                ->label(__('datatable.actions'))
//                ->excludeFromExport()
//                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        if ($this->page_type === 'approved') {
            return OfferRatings::where('status', 'approved');
        } else if ($this->page_type === 'pending') {
            return OfferRatings::where('status', 'pending');
        } else if ($this->page_type === 'unapproved') {
            return OfferRatings::where('status', 'unapproved');
        }
        return OfferRatings::selectRaw('*');
    }
}
