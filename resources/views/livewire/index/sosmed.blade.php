<div class="spilit-socail d-flex align-items-center gap-xxl-8 gap-xl-7 gap-5 me-5">
    @foreach ($sosmed as $item)
        <a href="{{ $item->value }}">
            {{ $item->key }}
        </a>
    @endforeach
</div>
<!-- Social -->
<!-- Contact Info -->
<div class="spilit-contact d-flex align-items-center gap-xxl-8 gap-xl-7 gap-md-5 gap-3">
    <a href="">
        Call : {{ $contact->phone }}
    </a>
    <a href="">
        mail : {{ $contact->email }}
    </a>
</div>
