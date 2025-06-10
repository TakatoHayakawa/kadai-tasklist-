@if (Auth::user()->is_favoriteing($micropost->favorite_id))
    {{-- お気に入り外すボタンのフォーム --}}
    <form method="POST" action="{{ route('user.unfavorite', $micropost->favorite_id) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-error btn-block normal-case" 
            onclick="return confirm('id = {{$micropost->favorite_id }} のお気に入りから外します。よろしいですか？')">Unfavorite</button>
    </form>
@else
    {{-- お気に入りボタンのフォーム --}}
    <form method="POST" action="{{ route('user.favorite', $micropost->favorite_id) }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-block normal-case">Favorite</button>
    </form>
@endif
