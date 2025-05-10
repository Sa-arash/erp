<x-filament-panels::page>
    <style>
        * {
            box-sizing: border-box;
        }

        .org-chart {
            text-align: center;
            font-family: sans-serif;
        }

        .org-chart ul {
            padding-top: 4px;
            position: relative;
            display: flex;
            justify-content: center;
        }

        .org-chart ul ul {
            padding-top: 10px;
        }

        .org-chart li {
            list-style: none;
            position: relative;
            padding: 4px 3px 0 3px;
            display: inline-block;
            vertical-align: top;
        }





        .org-chart li::after {
            right: -50%;
            border-left: 1px solid #ccc;
        }

        /* حذف خطوط برای افراد بدون فرزند */
        .org-chart li:only-child::before,
        .org-chart li:only-child::after {
            display: none;
        }

        /* خطوط برای اولین و آخرین فرزند */
        .org-chart li:first-child::before,
        .org-chart li:last-child::after {
            border: 0 none;
        }

        .org-chart li:last-child::before {
            border-right: 1px solid #ccc;
            border-radius: 0 5px 0 0;
        }

        .org-chart li:first-child::after {
            border-left: 1px solid #ccc;
            border-radius: 5px 0 0 0;
        }

        /* استایل شخص */
        .person {
            background: #fefefe;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 4px;
            display: block;
            margin: 0 auto;
            text-align: center;
            min-width: 80px;
            max-width: 100px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .person img {
            display: block;
            margin: 0 auto;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .person h6 {
            margin: 2px 0 2px;
            font-size: 12px!important;
        }

        .person p {
            margin: 0;
            font-size: 10px!important;
            color: #888;
        }

        /* گروه زیرمجموعه‌ها */
        .subordinates-group {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        /* رنگ‌ها برای گروه‌ها */
        .group-ceo {
            border-color: #3399ff;
            background-color: #e6f2ff;
        }

        .group-manager {
            border-color: #2ecc71;
            background-color: #e0ffe0;
        }

        .group-employee {
            border-color: #f1c40f;
            background-color: #fff9e6;
        }

        .group-owner {
            border-color: #e67e22;
            background-color: #f5e1da;
        }

        /* کانتینر */
        .org-chart-container {
            overflow: auto;
            max-width: 100%;
            max-height: 80vh;
            padding: 10px;
        }

        /* اطمینان از مناسب بودن سایز */
        .org-chart {
            min-width: 700px;
            display: inline-block;
        }



    </style>
    <div style="margin-bottom: 10px; text-align: center;">
        <button onclick="zoomOut()" style="padding: 5px 10px; margin-right: 10px;">➖ Zoom Out</button>
        <button onclick="zoomIn()" style="padding: 5px 10px; margin-right: 10px;">➕ Zoom In</button>
        <button onclick="resetZoom()" style="padding: 5px 10px;">🔄 Reset</button>
    </div>



    @php
        if (!function_exists('renderOrgTree')) {
            function renderOrgTree($employee, $depth = 0, $maxDepth = 4) {
                if ($depth > $maxDepth) return;

                $img = $employee->media?->where('collection_name', 'images')?->first()?->original_url;
                if (!$img) {
                    $img = $employee->gender == "male" ? asset('img/user.png') : asset('img/female.png');
                }

                echo '<li>';
                echo '<div class="person">';
                echo '<img src="' . $img . '" alt="' . $employee->name . '" loading="lazy" />';
                echo '<h6>' . $employee->fullName . '</h6>';
                echo '<p>' . ucfirst($employee->position->title) . '</p>';
                echo '</div>';

                if ($employee->subordinates->count()) {
                    $subordinates = $employee->subordinates;

                    // ✅ اگر بالادستی نداره، یعنی CEO هست → همه رو تو یه گروه بیار
                    $isCeo = is_null($employee->manager_id);
                    $chunks = $subordinates->chunk($isCeo ? 1000 : 4);

                    // 🎨 رنگ روشن رندوم برای گروه
                    $bgColor = $employee->department?->color?$employee->department?->color:sprintf('#%06X', mt_rand(0xDDDDDD, 0xFFFFFF));

                    echo '<div class="subordinates-group" style="
                        background-color: ' . $bgColor . ';
                        border: 1px dashed #999;
                        border-radius: 8px;
                        padding: 10px;
                        margin-top: 10px;
                    ">';

                    foreach ($chunks as $group) {
                        echo '<ul>';
                        foreach ($group as $sub) {
                            renderOrgTree($sub, $depth + 1, $maxDepth);
                        }
                        echo '</ul>';
                    }

                    echo '</div>';
                }

                echo '</li>';
            }
        }


        $topManagers = cache()->remember('top_managers_' . getCompany()->id, 60, function() {
            return \App\Models\Employee::with([
                'department',
                'media',
                'position',
                'subordinates',
                'subordinates.media',
                'subordinates.position',
                'subordinates.subordinates',
                'subordinates.subordinates.media',
                'subordinates.subordinates.position',
                'subordinates.subordinates.subordinates',
                'subordinates.subordinates.subordinates.subordinates',
            ])
            ->where('company_id', getCompany()->id)
            ->whereNull('manager_id')
            ->get();
        });
    @endphp

    <div class="org-chart-container">
        <div class="org-chart">
            <ul>
                @foreach($topManagers as $manager)
                    @php renderOrgTree($manager); @endphp
                @endforeach
            </ul>
        </div>
    </div>
    <script>
        let scale = 1;
        const chart = document.querySelector('.org-chart');
        const container = document.querySelector('.org-chart-container');

        function applyZoom() {
            chart.style.transform = `scale(${scale})`;
            chart.style.transformOrigin = 'top center';

            // بعد از زوم، مرکز چارت را به وسط اسکرول کن
            setTimeout(() => {
                const scrollLeft = (chart.offsetWidth * scale - container.clientWidth) / 2;
                container.scrollLeft = scrollLeft;
            }, 50); // کمی تأخیر برای اعمال scale
        }

        function zoomIn() {
            scale += 0.1;
            applyZoom();
        }

        function zoomOut() {
            scale = Math.max(0.2, scale - 0.1);
            applyZoom();
        }

        function resetZoom() {
            scale = 1;
            applyZoom();
        }

        document.addEventListener("DOMContentLoaded", applyZoom);
    </script>


</x-filament-panels::page>
