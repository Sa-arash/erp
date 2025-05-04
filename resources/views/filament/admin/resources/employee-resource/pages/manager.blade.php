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
            padding-top: 10px; /* کمتر از قبل */
            position: relative;
            display: flex;
            justify-content: center;
        }

        .org-chart ul ul {
            padding-top: 20px; /* کمتر از قبل */
        }

        .org-chart li {
            list-style: none;
            position: relative;
            padding: 10px 3px 0 3px; /* کمتر */
        }

        .org-chart li::before, .org-chart li::after {
            content: '';
            position: absolute;
            top: 0;
            border-top: 1px solid #ccc;
            width: 50%;
            height: 15px;
        }

        .org-chart li::before {
            left: -50%;
            border-right: 1px solid #ccc;
        }

        .org-chart li::after {
            right: -50%;
            border-left: 1px solid #ccc;
        }

        .org-chart li:only-child::before,
        .org-chart li:only-child::after {
            display: none;
        }

        .org-chart li:only-child {
            padding-top: 0;
        }

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

        .person {
            background: #fefefe;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 6px;
            display: block;
            margin: 0 auto;
            text-align: center;
            min-width: 100px;
            max-width: 120px; /* کوچکتر از قبل */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .person img {
            display: block;
            margin: 0 auto;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .person h3 {
            margin: 6px 0 3px;
            font-size: 13px; /* کوچکتر */
        }

        .person p {
            margin: 0;
            font-size: 11px;
            color: #888;
        }

        .owner {
            background-color: #f5e1da;
        }

        .ceo {
            background-color: #e6f2ff;
        }

        .manager {
            background-color: #e0ffe0;
        }

        .employee {
            background-color: #fff9e6;
        }

        .org-chart-container {
            overflow: auto;
            max-width: 100%;
            max-height: 80vh;
            padding: 20px;
        }

        .org-chart {
            min-width: 1000px; /* عرض حداقلی برای چارت‌های بزرگ */
            display: inline-block;
        }
    </style>
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
                echo '<h3>' . $employee->fullName . '</h3>';
                echo '<p>' . ucfirst($employee->position->title) . '</p>';
                echo '</div>';

                if ($employee->subordinates->count()) {
                    echo '<ul>';
                    foreach ($employee->subordinates as $sub) {
                        renderOrgTree($sub, $depth + 1, $maxDepth);
                    }
                    echo '</ul>';
                }

                echo '</li>';
            }
        }



       $topManagers = \App\Models\Employee::with([
           'media',
        'subordinates',
        'subordinates.subordinates',
        'subordinates.subordinates.subordinates',
        'subordinates.subordinates.subordinates.subordinates',
    ])
      ->where('company_id', getCompany()->id)->whereNull('manager_id')
      ->get();
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


</x-filament-panels::page>
