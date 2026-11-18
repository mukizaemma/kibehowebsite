@extends('desk.layout', ['deskNav' => 'pos'])
@section('title', 'Menu')
@section('content')
<div class="desk-pagehead">
    <div>
        <h1>Menu</h1>
        <p>A price change does not change a line already stored on a bill.</p>
    </div>
</div>
@if(auth()->user()->allowsDeskOperation('menu'))
    <div class="row g-3 mb-3">
        <div class="col-md-5">
            <form class="desk-card desk-form" method="post" action="{{ route('desk.pos.menu.categories.store') }}">
                @csrf
                <label for="category_name">Category</label>
                <input id="category_name" name="name" required>
                <button class="btn btn-outline-dark mt-2" type="submit">Add category</button>
            </form>
        </div>
        <div class="col-md-7">
            <form class="desk-card desk-form" method="post" action="{{ route('desk.pos.menu.items.store') }}">
                @csrf
                <div class="desk-pair">
                    <div class="field"><label for="item_name">Item</label><input id="item_name" name="name" required></div>
                    <div class="field">
                        <label for="menu_category_id">Category</label>
                        <select id="menu_category_id" name="menu_category_id">
                            <option value="">None</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="desk-pair">
                    <div class="field"><label for="price">Price</label><input id="price" name="price" type="number" min="0" step="0.01" required></div>
                    <div class="field">
                        <label for="item_currency">Currency</label>
                        <select id="item_currency" name="currency"><option value="usd">USD</option><option value="rwf">RWF</option></select>
                    </div>
                </div>
                <button class="btn btn-dark" type="submit">Add item</button>
            </form>
        </div>
    </div>
@endif
<div class="desk-card">
    <div class="table-responsive">
        <table class="desk-table">
            <thead><tr><th>Item</th><th class="num">Price</th><th class="actions">Action</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            @if(auth()->user()->allowsDeskOperation('menu'))
                                <form method="post" action="{{ route('desk.pos.menu.items.update', $item) }}" class="d-flex flex-wrap gap-2 align-items-center">
                                    @csrf
                                    <input name="name" value="{{ $item->name }}" style="min-height:44px;">
                                    <input name="price" type="number" min="0" step="0.01" value="{{ $item->price }}" style="width:120px;min-height:44px;">
                                    <select name="currency" style="min-height:44px;">
                                        <option value="usd" @selected($item->currency === 'usd')>USD</option>
                                        <option value="rwf" @selected($item->currency === 'rwf')>RWF</option>
                                    </select>
                                    <label class="desk-check mb-0"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> Active</label>
                                    <button class="btn btn-outline-dark btn-sm" type="submit">Save</button>
                                </form>
                            @else
                                {{ $item->name }}
                            @endif
                        </td>
                        <td class="num">{{ desk_money((float) $item->price, $item->currency) }}</td>
                        <td class="actions">
                            @if(auth()->user()->allowsDeskOperation('menu'))
                                <a href="{{ route('desk.pos.menu.items.delete.form', $item) }}">Delete</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="desk-empty" colspan="3">No menu items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
