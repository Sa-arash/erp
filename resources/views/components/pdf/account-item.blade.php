<ul class="item-list" style="margin-left: 10px;">
    @foreach ($items as $key => $item)
        @if ($key && $key !== '' && $key !== 0)
            <li>
                <strong>{{ $key }}:</strong> {{ number_format($item['sum']) }}
            </li>
            @if (isset($item['item']) && count($item['item']))
                @include('components.pdf.account-item', ['items' => $item['item']])
            @endif
        @endif
    @endforeach

</ul>
