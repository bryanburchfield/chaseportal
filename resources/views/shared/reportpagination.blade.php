@if($params['totpages'] > 1)
<?php
        $curpage = $params['curpage'];
        $lastpage = $params['totpages'];
        $pagesize = $params['pagesize'];
        $totrows = $params['totrows'];

        // show at least 2 pages either side of current, but 5 total (if we have that many)
        if($curpage <= 2) {
            $startpage = 1;
            $endpage = 5;
        } elseif($curpage >= ($lastpage - 2)) {
            $startpage = $lastpage - 4;
            $endpage = $lastpage;
        } else {
            $startpage = $curpage - 2;
            $endpage = $curpage + 2;
        }

        if($startpage < 1) {
            $startpage = 1;
        }

        if($endpage > $lastpage) {
            $endpage = $lastpage;
        }

        $nextpage = ($curpage == $lastpage) ? $lastpage : $curpage + 1;
        $prevpage = ($curpage == 1) ? 1 : $curpage - 1;

        // disable next/prev if we're at the beginning/end
        $prevpageclass = ($curpage == 1) ? 'class="disabled"' : '';
        $nextpageclass = ($curpage == $lastpage) ? 'class="disabled"' : '';
?>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item" {!! $prevpageclass !!}><a class="page-link" href="#" data-paglink="1">{{__('general.first')}}</a></li>
            <li class="page-item" {!! $prevpageclass !!}><a class="page-link" href="#" data-paglink="{{ $prevpage }}">&laquo;</a></li>

            @for($i = $startpage; $i <= $endpage; $i++)
                <li 
                @if($i == $curpage)
                    class=" page-item active"
                @else
                    class=" page-item"
                @endif
                ><a class="page-link" href="#" data-paglink="{{ $i}}">{{ $i }}</a></li>
            @endfor
            <li class="page-item" {!! $nextpageclass !!}><a class="page-link" href="#" data-paglink="{{ $nextpage }}">&raquo;</a></li>
            <li class="page-item" {!! $nextpageclass !!}><a class="page-link" href="#" data-paglink="{{ $lastpage }}">{{__('general.last')}}</a></li>
        </ul>
    </nav>

    <div class="pag_dets mt-3">
        <p>{{__('general.page')}}: <input type="number" min="1" max="{{ $lastpage }}" name="curpage" data-prevval="{{ $curpage }}" class="sm-input form-control pag_input curpage" value="{{ $curpage }}"> {{__('general.of')}} {{ $lastpage }}
            ({{__('general.page_size')}}: <input type="number" name="pagesize" class="sm-input form-control pag_input pagesize" data-prevval="{{ $pagesize }}" value="{{ $pagesize }}"> {{__('general.total_rows')}}: {{ $totrows }})
        </p>
        <input type="hidden" name="totrows" class="sm-input form-control pag_input totrows" data-prevval="{{ $totrows }}" value="{{ $totrows }}">
    </div>
    <div class="alert alert-danger errors pag_report_errors"></div>
@endif
