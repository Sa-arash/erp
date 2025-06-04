<x-filament-panels::page>
    <script src="https://unpkg.com/gojs/release/go.js"></script>
    <style>
        #goChartDiv {
            width: 100%;
            height: 80vh;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
    </style>

    @php


        $treeData = \App\Models\Employee::with(['position', 'manager', 'media', 'department'])
     ->where('company_id', getCompany()->id)
     ->get()
     ->map(function ($employee) {
         return [
             'key' => $employee->id,
             'name' => $employee->fullName,
             'title' => optional($employee->position)->title,
             'parent' => $employee->manager_id,
             'image' => $employee->media?->where('collection_name', 'images')->first()?->original_url
                       ?? asset($employee->gender == 'male' ? 'img/user.png' : 'img/female.png'),
             'color' => optional($employee->department)->color ?? '#f0f0f0',
         ];
     })
     ->values();


    @endphp

    <div style="margin-bottom: 10px; text-align: center;">
        <button onclick="zoomOut()" style="padding: 5px 10px; margin-right: 10px;">➖ Zoom Out</button>
        <button onclick="zoomIn()" style="padding: 5px 10px; margin-right: 10px;">➕ Zoom In</button>
        <button onclick="resetZoom()" style="padding: 5px 10px;">🔄 Reset</button>
    </div>

    <div id="goChartDiv"></div>

    <script>
        const $ = go.GraphObject.make;
        let myDiagram;
        let diagramScale = 1;

        document.addEventListener("DOMContentLoaded", () => {
            myDiagram = $(go.Diagram, "goChartDiv", {
                initialAutoScale: go.Diagram.Uniform,
                layout: $(go.TreeLayout, {
                    angle: 90,
                    layerSpacing: 30
                }),
                "undoManager.isEnabled": true
            });

            myDiagram.nodeTemplate =
                $(go.Node, "Auto",
                    $(go.Shape, "RoundedRectangle",
                        {
                            stroke: "#cccccc", strokeWidth: 1
                        },
                        new go.Binding("fill", "color")),  // ← اینجا رنگ از داده می‌گیرد
                    $(go.Panel, "Vertical",
                        $(go.Picture,
                            {
                                name: "Picture",
                                desiredSize: new go.Size(50, 50),
                                margin: 4
                            },
                            new go.Binding("source", "image")),
                        $(go.TextBlock,
                            {
                                font: "bold 12pt Segoe UI, sans-serif",
                                stroke: "#333",
                                margin: 4,
                                textAlign: "center"
                            },
                            new go.Binding("text", "name")),
                        $(go.TextBlock,
                            {
                                font: "10pt Segoe UI, sans-serif",
                                stroke: "#666",
                                textAlign: "center"
                            },
                            new go.Binding("text", "title"))
                    )
                );


            myDiagram.linkTemplate = $(go.Link,
                {routing: go.Link.Orthogonal, corner: 5},
                $(go.Shape, {strokeWidth: 1, stroke: "#ccc"})
            );

            const nodeDataArray = @json(collect($treeData)->map(function ($item) {
    $item['parent'] = $item['parent'] ?? 'undefined';
    return $item;
}));

            myDiagram.model = new go.TreeModel(nodeDataArray);
        });

        function zoomIn() {
            diagramScale += 0.1;
            myDiagram.scale = diagramScale;
        }

        function zoomOut() {
            diagramScale = Math.max(0.2, diagramScale - 0.1);
            myDiagram.scale = diagramScale;
        }

        function resetZoom() {
            diagramScale = 1;
            myDiagram.scale = 1;
        }
    </script>
</x-filament-panels::page>
