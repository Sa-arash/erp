@if (isset($data))
    <script>
        window.filamentData = @js($data)
    </script>
@endif

@foreach ($assets as $asset)
    @if (! $asset->isLoadedOnRequest())
        {{ $asset->getHtml() }}
    @endif
@endforeach

@auth()
    <style>
        :root {
            @foreach ($cssVariables ?? [] as $cssVariableName => $cssVariableValue) --{{ $cssVariableName }}:{{ $cssVariableValue }}; @endforeach

    }
        .animate-spin1 {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <div id="globalLoader" class="fixed inset-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="text-center">
            <!-- لوگوی شما -->


            <!-- اسپینر -->
            <div class="inline-block animate-spin1 rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>


        </div>
    </div>


    <script>
    window.userId = {{ auth()->id() }};
    function storeCurrentUrl() {
        // دریافت URL فعلی
        const currentUrl = window.location.href;

        // دریافت لیست URLهای ذخیره شده یا ایجاد آرایه خالی
        let storedUrls = JSON.parse(localStorage.getItem('visitedUrls')) || [];

        // اگر URL فعلی از قبل وجود دارد، آن را حذف می‌کنیم
        storedUrls = storedUrls.filter(url => url !== currentUrl);

        storedUrls.unshift(currentUrl);

        if (storedUrls.length > 5) {
            storedUrls = storedUrls.slice(0, 5);
        }

        // ذخیره در localStorage
        localStorage.setItem('visitedUrls', JSON.stringify(storedUrls));

    }
    storeCurrentUrl()
    window.addEventListener('load', function() {
        const loader = document.querySelectorAll('#globalLoader');
        setTimeout(() => loader.forEach((item)=>{
            item.style.opacity = '0';
            item.remove()
        }), 300);
    });
</script>
@endauth
