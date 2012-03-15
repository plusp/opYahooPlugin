<!DOCTYPE html>
<html>
        <head>
                <meta http-equiv="content-type" content="text/html; charset=UTF-8">
<script src="http://www.google.com/jsapi"></script>
<script>
  google.load("jquery", "1.7.1");
</script>
<script type="text/javascript">
<!--
/* 3桁区切りのカンマを挿入 */
function addFigure(str) {
var num = new String(str).replace(/,/g, "");
while(num != (num = num.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
return num;
}

$(function(){
        $('#query').keypress(function (e) {
                if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                        // 検索欄でEnterキーを押した場合、検索ボタンがクリックされる
                        $('#search').trigger('click');
                }
        });
        $('#search').click(function(){

                $('#result').empty();
                var query = $('#query').val();
                if( $.isEmptyObject(query) ){
                        // 検索欄が空白の場合、処理を終了
                        return;
                }

                var appid =  '1LBv.HWxg64jnWEM7RA0gxKLdLD0RFOL9vS1LrU5fXapl.JHB8hDDUqNy3ZSQwjK';
                var url = 'http://auctions.yahooapis.jp/AuctionWebService/V2/json/search?appid=' + appid + '&query=' + query;
                var topUrl = 'http://auctions.search.yahoo.co.jp/search?p=' + query;

                var times = 5; // 検索結果の表示件数を指定
                var now = (new Date()).getTime(); // 残り時間の計算用

                $.ajax({
                        url: url,
                        dataType: 'jsonp',
                        success: function(json){

                                function getAuctionSearchResult(Item){
                                        $('<div class="title"></div>').html('<a href="' + Item.AuctionItemUrl + '">' + Item.Title + '</a>').appendTo('#result');
                                        $('<a href="' + Item.AuctionItemUrl + '">' + Item.Title + '</a>').html('<img src="' + Item.Image + '" />').appendTo('#result');
                                        $('<div></div>').text('入札：' + Item.Bids + '件').appendTo('#result');
                                        $('<div></div>').html('現在価格：' + '<span class="currentPrice">' + addFigure(Math.floor(Item.CurrentPrice)) + '円' + '</span>').appendTo('#result');
                                        if( ! $.isEmptyObject(Item.BidOrBuy) ){
                                                // 即決価格がない場合、表示しない
                                                $('<div class="buyitnow"></div>').text('即決価格：' + addFigure(Math.floor(Item.BidOrBuy)) + '円').appendTo('#result');
                                        }

                                        var EndTime = Item.EndTime;
                                        // RFC3339形式で格納されているので分割
                                        var EndYear = EndTime.split('-')[0];
                                        var EndMonth = EndTime.split('-')[1];
                                        var EndDay = EndTime.split('-')[2].split('T')[0];
                                        var EndHour = EndTime.split('T')[1].split(':')[0];
                                        var EndMinute = EndTime.split('T')[1].split(':')[1];
                                        var EndSecond = EndTime.split('T')[1].split(':')[2].split('+')[0];
                                        // var EndDate = EndYear + '年' + EndMonth + '月' + EndDay + '日' + EndHour + '時' + EndMinute + '分';
                                        // $('<div></div>').text('終了予定日時：' + EndDate).appendTo('#result');

                                        // 月の形式をJSに合わせる
                                        var EndMonthJs = EndMonth;
                                        if(EndMonthJs.match(/^0/)){
                                                // 月の表示が0で始まる場合、0を削除
                                                EndMonthJs = EndMonthJs.split('0')[1];
                                        }
                                        EndMonthJs -= 1; // 月の形式を0から11にする

                                        // 残り時間の計算
                                        var targetDate = (new Date(EndYear, EndMonthJs, EndDay, EndHour, EndMinute, EndSecond)).getTime();
                                        var remainingTime = (targetDate - now);
                                        var remainingDay = remainingTime / (1000 * 60 * 60 * 24);
                                        if ( Math.floor(remainingDay) > 0 ) {
                                                $('<div></div>').text('残り時間：約' + Math.floor(remainingDay) + '日').appendTo('#result');
                                        } else {
                                                var remainingHour = remainingTime / (1000 * 60 * 60);
                                                if ( Math.floor(remainingHour) > 0 ) {
                                                        $('<div></div>').text('残り時間：約' + Math.ceil(remainingHour) + '時間').appendTo('#result');
                                                } else {
                                                        var  remainingMinute = remainingTime / (1000 * 60);
                                                        if ( Math.floor(remainingMinute) > 0 ) {
                                                        $('<div></div>').html('残り時間：約' + '<span class="minutes">' + Math.ceil(remainingMinute) + '分' + '</span>').appendTo('#result');
                                                        }
                                                }
                                        }
                                }

                                function getAuctionSearchResultFooter(){
                                        $('.title:first').addClass('titleTop');
                                        $('<div id="topUrl"><a href="' + topUrl + '">' + 'Yahoo!オークション<span class="small">で</span>もっと見る</a></div>').appendTo('#result');
                                }

                                var totalResults = json.ResultSet["@attributes"].totalResultsAvailable;

                                if( totalResults == 0 ) {
                                        // 検索結果が0件の場合
                                        $('<div id="resultInfo"></div>').html('<span class="query">' + query + '</span>で検索した結果：見つかりませんでした').appendTo('#result');
                                        return;
                                }

                                if( totalResults == 1 ) {
                                        // 検索結果が1件の場合
                                        $('<div id="resultInfo"></div>').html('<span class="query">' + query + '</span>で検索した結果：<span class="query">1から' + totalResults + '</span>件目 / 約<span class="query">' + addFigure(totalResults) + '</span>件').appendTo('#result');
                                        var Item = json.ResultSet.Result.Item;
                                        getAuctionSearchResult(Item);
                                        getAuctionSearchResultFooter();
                                        return;
                                }

                                if( totalResults <= times ) {
                                        // 検索結果が指定した表示件数以下の場合
                                        $('<div id="resultInfo"></div>').html('<span class="query">' + query + '</span>で検索した結果：<span class="query">1から' + totalResults + '</span>件目 / 約<span class="query">' + addFigure(totalResults) + '</span>件').appendTo('#result');
                                } else {
                                        // 検索結果が指定した表示件数以上の場合
                                        $('<div id="resultInfo"></div>').html('<span class="query">' + query + '</span>で検索した結果：約<span class="query">' + addFigure(totalResults) + '</span>件中、<span class="query">1から' + times + '</span>件目を表示').appendTo('#result');
                                }

                                var Items = json.ResultSet.Result.Item;
                                $.each(Items, function(i, Item){
                                        if( i == times ) {
                                                // 指定回数以上は検索結果を表示しない
                                                return false;
                                        }
                                        getAuctionSearchResult(Item);
                                });
                                getAuctionSearchResultFooter();
                        }
                });

        });
});
//-->
</script>
<style type="text/css">
<!--
#resultInfo {
        background: #fccf46;
        padding: 0.2em 0.5em;
        margin-top: 5px;
        line-height: 1.0;
        }
#topUrl {
        position: relative;
        height: 3em;
}
#topUrl a {
        display: block;
        position: absolute;
        text-decoration: none;
        color: #fff;
        padding: 0.2em 0.5em;
        margin-top: 1em;
        line-height: 1.0;
        border: 1px solid #000;
        border: 1px solid rgba(0,0,0,.3);
        background: -moz-linear-gradient(top, #a0d546 25%, #7fc013 100%);
        background: -webkit-linear-gradient(top, #7fc013 25%, #a0d546 100%);
        background: -o-linear-gradient(bottom, #25b24c, #d3b55b 150%);
        background: -ms-linear-gradient(bottom, #25b24c, #d3b55b 150%);
        background: linear-gradient(bottom, #25b24c, #d3b55b 150%);
        -moz-border-radius: 4px;
        -o-border-radius: 4px;
        -webkit-border-radius: 4px;
        border-radius: 4px;
        -moz-box-shadow: 1px 1px 1px 0 rgba(0,0,0,.7);
        -webkit-box-shadow: 1px 1px 1px 0 rgba(0,0,0,.7);
        -o-box-shadow: 1px 1px 1px 0 rgba(0,0,0,.7);
        -ms-box-shadow: 1px 1px 1px 0 rgba(0,0,0,.7);
        }
#topUrl a:hover {
        top: 1px;
        left: 1px;
        -moz-box-shadow: none;
        -webkit-box-shadow: none;
        -o-box-shadow: none;
        -ms-box-shadow: none;
        }
.small {
        font-size: 90%;
        margin: 0 0.1em;
        }
.title {
        padding-top: 0.5em;
        border-top: 1px solid #ddd;
        margin-top: 0.5em;
        }
.titleTop {
        border-top: none;
        }
.query,
.currentPrice {
        font-weight: bold;
        }
.minutes {
        color: #f00;
        }
-->
</style>
                <title>Yahoo Api Test</title>
        </head>
        <body>
                <input id="query" name="query" value="" />
                <input id="search" type="button" value="検索" />
                <div id="result"></div>
        </body>
</html>

