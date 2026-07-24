<?php

namespace App\Http\Livewire\Community\Chats;

use App\Models\Chats\ChatChannel;
use Illuminate\Database\Eloquent\Builder;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;

class CommunityChatsInquiryComponent extends LivewireDatatable
{

    /**
     * set variables
     */
    public $exportable = false;
    public $hideable = 'select';
    /*public $beforeTableSlot = 'livewire.datatables.selected';*/
    public $afterTableSlot = '';
    /*public string $afterTableSlot2 = 'modals.community.comments.restore';*/
    public $model = ChatChannel::class;
    public array $chat;
    public array $deleteModalTexts;
    public array $editModalTexts;
    public bool $has_delete = false;
    public bool $has_restore = false;

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
                ->searchable()
                ->width('60px'),
            Column::callback(['id', 'created_at'], function ($id) {
                $chat_users = ChatChannel::findOrFail($id)
                    ->users()
                    ->get();
                $users = '';
                foreach ($chat_users as $user){
                    $user_type = __("pages/community/chats/inquiry.datatable.".optional($user->user)->user_type );
                    $users.= "<li>{$user_type}: " . optional($user->user)->name . " (#" .optional($user->user)->id ." )</li>";
                }
                return "<ul class='list-unstyled'>$users</ul>";
            })
                ->label(__('pages/community/chats/inquiry.datatable.users')),
            DateColumn::name('last_message_at')
                ->label(__('pages/community/chats/inquiry.datatable.last_message'))
                ->filterable()
                ->searchable()
                ->format('Y-m-d h:i A'),
            Column::callback(['id', 'last_message_at'], function ($id) {
                return view('admin.pages.community.chats.table-actions', ['id' => $id]);
            })
                ->label(__('datatable.actions'))
                ->alignCenter()
                ->excludeFromExport()
                ->unsortable(),
        ];
    }

    /**
     * set query to render data
     * @return Builder
     */
    public function builder(): Builder
    {
        return ChatChannel::selectRaw('*');
    }
}
