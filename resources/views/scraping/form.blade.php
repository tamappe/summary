<form method="POST" action="/rss">
    <dl>
        <dt>rss url:</dt>
        <dd><input type="text" name="rss"></dd>
    </dl>

    <input type="submit" value="送信">
    <input type="hidden"  name="_token" value="{{ csrf_token() }}">
</form>


<form method="POST" action="/atom">
    <dl>
        <dt>atom url:</dt>
        <dd><input type="text" name="atom"></dd>
    </dl>
    <input type="submit" value="送信">
    <input type="hidden"  name="_token" value="{{ csrf_token() }}">
</form>