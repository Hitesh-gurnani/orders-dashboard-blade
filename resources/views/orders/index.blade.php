<x-layout
    title="Orders"
    :theme="$theme"
    :density="$density"
    :nav="$nav"
    current="orders"
    :period="$period"
    :query="$query">

    <section class="strip" aria-label="Key metrics">
        @foreach ($metrics as $metric)
            <x-stat
                :label="$metric['label']"
                :value="$metric['value']"
                :delta="$metric['delta']"
                :direction="$metric['direction']"
                :series="$metric['series']"
                :caption="$metric['caption']" />
        @endforeach
    </section>

    {{--
        A real <form method="get"> with a real submit button. There is no
        JavaScript in this project at all -- filtering, sorting, pagination,
        theme and density are links and a GET form, which is what they should
        have been anyway. See the README: the debounced auto-submit this nearly
        shipped with would have moved focus mid-keystroke.
    --}}
    <form class="filters" method="get" action="/" role="search">
        <div class="filters__chips">
            @foreach ($chips as $chip)
                <x-filter-chip
                    :href="$chip['href']"
                    :label="$chip['label']"
                    :count="$chip['count']"
                    :current="$chip['current']" />
            @endforeach
        </div>

        <div class="filters__search">
            <label class="filters__label" for="q">Search</label>
            <input class="filters__input"
                type="search"
                id="q"
                name="q"
                value="{{ $search }}"
                maxlength="64"
                autocomplete="off"
                placeholder="Customer or order number">
            <button class="filters__submit" type="submit">Apply</button>
        </div>

        {{-- A GET form replaces the whole query string, so the state that is not
             in a field has to ride along or it is silently dropped on submit. --}}
        @foreach ($carry as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>

    {{-- Keyboard-focusable scroll region: below 680px this scrolls sideways, and
         a scrollable area that cannot be reached by keyboard is a trap. --}}
    <div class="orders__scroll" id="orders" role="region" aria-label="Orders" tabindex="0">
        <table class="orders">
            <caption class="sr-only">
                Orders for {{ $period }}{{ $filterSummary }}. Sorted by {{ $state['label'] }}.
            </caption>

            <colgroup>
                <col class="col-id">
                <col class="col-customer">
                <col class="col-placed">
                <col class="col-items">
                <col class="col-total">
                <col class="col-status">
            </colgroup>

            <thead>
                <tr>
                    <x-table.sort-header column="number" label="Order" :state="$state" />
                    <x-table.sort-header column="customer" label="Customer" :state="$state" />
                    <x-table.sort-header column="placed" label="Placed" :state="$state" class="col-placed" />
                    <x-table.sort-header column="items" label="Items" :state="$state" align="end" class="col-items" />
                    <x-table.sort-header column="total" label="Total (USD)" :state="$state" align="end" />
                    <th scope="col" class="orders__th">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $order)
                    <x-table.row :order="$order" />
                @empty
                    <x-empty-state
                        :title="$empty['title']"
                        :body="$empty['body']"
                        :filters="$empty['filters']"
                        :clear="$empty['clear']" />
                @endforelse
            </tbody>

            @if ($rows)
                {{-- The total for the ACTIVE FILTER across every matching order,
                     not for the twenty rows that happen to be on this page. It
                     uses the same function as the Revenue card, so with no
                     filter applied the two agree exactly -- which is a claim a
                     sceptical reviewer can check by summing the column. --}}
                <tfoot>
                    <tr>
                        <td class="orders__foot" colspan="4">
                            {{ $matched }} {{ $matched === 1 ? 'order' : 'orders' }}<span class="orders__foot-note">net of cancelled</span>
                        </td>
                        <td class="orders__foot is-num">
                            <x-money :value="$netTotal" />
                        </td>
                        <td class="orders__foot"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if ($rows)
        <x-pagination
            :page="$page"
            :pages="$pages"
            :total="$total"
            :from="$from"
            :to="$to"
            :query="$query" />
    @endif
</x-layout>
