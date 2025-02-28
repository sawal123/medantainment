<div class="single-blog-post">
    <div class="post-featured-thumb w-100 mb-xxl-30 mb-xl-6 mb-5" data-aos="zoom-in"
        data-aos-duration="1400">
        <img src="{{asset('storage/'. $post->image)}}" alt="img" class="w-100">
    </div>
    <div class="post-content">
        <div class="post-marry d-flex align-items-center gap-xxl-8 gap-xl-6 gap-4 gap-3 mb-xxl-5 mb-xl-4 mb-lg-3 mb-3"
            data-aos="fade-left" data-aos-duration="1500">
            <span>
                Written by: {{$post->user->name}}
            </span>
            <i class="fas fa-circle white"></i>
            <span>
                {{date('d-m-Y', strtotime($post->created_at))}}
            </span>
        </div>
        <div class="mb-xxl-13 mb-xl-10 mb-lg-8 mb-7">
            <h5 class="white mb-xxl-5 mb-3" data-aos="fade-left" data-aos-duration="1600">
               {{$post->title}}</h5>
            <div class="">
                {!!$post->content!!}
            </div>
        </div>
       
    </div>
</div>