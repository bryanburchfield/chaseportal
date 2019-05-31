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
    <ul class="pagination pg-blue">
        <li {!! $prevpageclass !!}><a href="#" data-paglink="1">First</a></li>
        <li {!! $prevpageclass !!}><a href="#" data-paglink="{{ $prevpage }}">&laquo;</a></li>

        @for($i = $startpage; $i <= $endpage; $i++)
            <li 
            @if($i == $curpage)
                class="active"
            @endif
            ><a href="#" data-paglink="{{ $i}}">{{ $i }}</a></li>
        @endfor
        <li {!! $nextpageclass !!}><a href="#" data-paglink="{{ $nextpage }}">&raquo;</a></li>
        <li {!! $nextpageclass !!}><a href="#" data-paglink="{{ $lastpage }}">Last</a></li>
    </ul>
    <div class="pag_dets">
        <p>PAGE: <input type="number" min="1" max="{{ $lastpage }}" name="curpage" data-prevval="{{ $curpage }}" class="sm-input form-control pag_input curpage" value="{{ $curpage }}"> OF {{ $lastpage }}
            (Page size: <input type="number" name="pagesize" class="sm-input form-control pag_input pagesize" data-prevval="{{ $pagesize }}" value="{{ $pagesize }}"> Total rows: {{ $totrows }})
        </p>
        <input type="hidden" name="totrows" class="sm-input form-control pag_input totrows" data-prevval="{{ $totrows }}" value="{{ $totrows }}">
    </div>
    <div class="alert alert-danger errors pag_report_errors"></div>
@endif
