@extends('layouts.admin')

@section('title', 'Organization Hierarchy Map')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@3.1.0/dist/css/jquery.orgchart.min.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var link = document.querySelector('link[href*="jquery.orgchart.min.css"]');
        if (link) {

        } else {
            console.warn('OrgChart.js CSS not found.');
        }
    });
</script>
<style>
    /* Responsive container: limit max height to viewport minus header/footer and center content horizontally.
       Use flex layout so the chart sits at the top and doesn't leave a large bottom gap on smaller charts. */
    #tree-container {
        width: 100%;
        min-height: 360px;
        /* reasonable minimum */
        max-height: calc(100vh - 240px);
        /* fit inside viewport */
        border: 1px solid #ccc;
        overflow: auto;
        background-color: #f8f8f8;
        display: flex;
        align-items: flex-start;
        /* keep chart at top so no large bottom gap */
        justify-content: center;
        /* center horizontally */
        padding: 12px;
        box-sizing: border-box;
    }

    .orgchart {
        background: #fff;
        /* Ensure a visible background */
        min-height: 200px;
        min-width: 200px;
        max-width: 100%;
        box-sizing: border-box;
        /* allow the orgchart to shrink/expand; plugin often uses tables internally */
        display: inline-block;
    }

    /* Make internal tables responsive where possible */
    .orgchart table {
        width: auto !important;
        max-width: 100%;
    }

    .orgchart .node .title {
        background-color: #006666;
        /* Primary color */
    }

    .orgchart .node .content {
        border-color: #006666;
    }
</style>


@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h4>Organization Hierarchy Map</h4>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Organization</li>
                    <li class="breadcrumb-item active">Hierarchy Map</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div id="tree-container"></div>
        </div>
    </div>
</div>

<script>
    (function() {
            var hierarchyData = @json($hierarchyData);
            // Toggle preference: if true, prefer Cytoscape renderer even when the orgchart plugin is present
            var preferCytoscape = true;


            function loadScript(url, cb) {
                var s = document.createElement('script');
                s.src = url;
                s.async = true;
                s.onload = cb;
                s.onerror = function() {
                    console.error('Failed to load script:', url);
                    cb(new Error('Failed to load ' + url));
                };
                document.head.appendChild(s);
            }

            function loadCSS(url, cb) {
                var l = document.createElement('link');
                l.rel = 'stylesheet';
                l.href = url;
                l.onload = function() {
                    if (cb) cb();
                };
                l.onerror = function() {
                    console.error('Failed to load css:', url);
                    if (cb) cb(new Error('Failed to load css ' + url));
                };
                document.head.appendChild(l);
            }

            // Build orgchart data recursively
            function buildOrgChartData(nodeId, allNodes) {
                var node = allNodes[nodeId];
                if (!node) return null;
                var orgChartNode = {
                    'id': node.id,
                    'name': node.name,
                    'title': node.title,
                    'user_type': node.user_type,
                    'employee_id': node.employee_id,
                    'department': node.department,
                    'children': []
                };
                (node.children || []).forEach(function(childId) {
                    if (allNodes[childId]) {
                        var child = buildOrgChartData(childId, allNodes);
                        if (child) orgChartNode.children.push(child);
                    }
                });
                return orgChartNode;
            }

            function renderFallbackTree(root) {
                // Simple nested UL fallback to show hierarchy if orgchart fails
                function renderNode(node) {
                    var li = document.createElement('li');
                    li.innerHTML = '<strong>' + (node.name || 'N/A') + '</strong> <small>(' + (node.title || '') +
                        ')</small>';
                    if (node.children && node.children.length) {
                        var ul = document.createElement('ul');
                        node.children.forEach(function(c) {
                            ul.appendChild(renderNode(c));
                        });
                        li.appendChild(ul);
                    }
                    return li;
                }
                var container = document.getElementById('tree-container');
                container.innerHTML = '';
                var ul = document.createElement('ul');
                ul.style.listStyle = 'none';
                ul.appendChild(renderNode(root));
                container.appendChild(ul);
            }

            function initOrgChart(rootNode) {
                // If preferCytoscape is set, use it regardless of orgchart plugin presence
                if (!preferCytoscape && window.jQuery && typeof jQuery.fn.orgchart === 'function') {
                    $('#tree-container').orgchart({
                        data: rootNode,
                        init: 'initial'
                    });
                    // annotate which renderer was used

                    return;
                }

                // Prefer Cytoscape renderer
                renderCytoscapeOrgChart(rootNode, function(success) {
                    if (!success) {
                        // If Google Charts not available or failed, show a friendly warning + raw data and fallback tree
                        var container = document.getElementById('tree-container');
                        var msg = document.createElement('div');
                        msg.className = 'alert alert-warning';
                        msg.innerText =
                            'OrgChart plugin not available. Showing fallback tree. Check network/console for missing JS/CSS.';
                        container.innerHTML = '';
                        container.appendChild(msg);
                        // show raw data for debugging
                        var pre = document.createElement('pre');
                        pre.style.whiteSpace = 'pre-wrap';
                        pre.style.background = '#f6f8fa';
                        pre.style.padding = '10px';
                        pre.style.border = '1px solid #e1e4e8';
                        pre.textContent = JSON.stringify(rootNode, null, 2);
                        container.appendChild(pre);
                        // render fallback
                        renderFallbackTree(rootNode);
                    }
                });
            }
            @section('title', 'Organization Hierarchy Map')

                <
                style >
                /* Chart container and toolbar styles */
                #chart - toolbar {
                    display: flex;gap: 8 px;margin - bottom: 10 px;
                }
            #chart - toolbar.btn {
                padding: 6 px 10 px;font - size: 13 px;
            }
            #tree - container {
                width: 100 % ;height: calc(100 vh - 240 px);min -
                height: 360 px;border: 1 px solid #e3e3e3;background: #fff;
            }
            #tree - wrapper {
                    padding: 12 px;
                } <
                /style>

            @section('content') 
            <div class = "container-fluid" >
                <div class = "page-title" >
                <div class = "row" >
                <div class = "col-6" >
                <h4> Organization Hierarchy Map </h4> </div >
                <div class = "col-6" >
                <ol class = "breadcrumb" >
                <li class = "breadcrumb-item" > < a href = "{{ route('dashboard') }}" >
                <svg class = "stroke-icon" >
                <use href = "{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}" > /<use> </svg> </a> </li >
                <li class = "breadcrumb-item" > Organization </li>
                <li class = "breadcrumb-item active" > Hierarchy Map </li> </ol> </div> </div> </div> </div> </div>

                <div class = "container-fluid" >
                <div class = "card" >
                <div class = "card-body" >
                <div id = "chart-toolbar" class = "mb-2" >
            <button id = "btn-fit" class = "btn btn-sm btn-outline-primary" > Fit </button> <button id = "btn-zoom-in" class = "btn btn-sm btn-outline-secondary" > Zoom In </button> <button id = "btn-zoom-out" class = "btn btn-sm btn-outline-secondary" > Zoom Out </button> </div > <div id = "tree-wrapper" >
                <div id = "tree-container" > </div> </div> </div> </div> </div>

                <script>
                (function() {
                    // Use vis-network for a clean hierarchical interactive chart
                    var data = @json($hierarchyData);

                    function loadScript(url, cb) {
                        var s = document.createElement('script');
                        s.src = url;
                        s.async = true;
                        s.onload = cb;
                        s.onerror = function() {
                            cb(new Error('load failed'));
                        };
                        document.head.appendChild(s);
                    }

                    function initVis(nodes, edges) {
                        var container = document.getElementById('tree-container');
                        container.innerHTML = '';
                        var network = new vis.Network(container, {
                            nodes: new vis.DataSet(nodes),
                            edges: new vis.DataSet(edges)
                        }, {
                            layout: {
                                hierarchical: {
                                    enabled: true,
                                    direction: 'UD',
                                    sortMethod: 'directed',
                                    nodeSpacing: 200
                                }
                            },
                            interaction: {
                                hover: true,
                                navigationButtons: true,
                                keyboard: true
                            },
                            nodes: {
                                shape: 'box',
                                margin: 10,
                                font: {
                                    multi: true,
                                    size: 14
                                },
                                color: {
                                    border: '#006666',
                                    background: '#ffffff'
                                }
                            },
                            edges: {
                                arrows: {
                                    to: false
                                },
                                color: '#888'
                            },
                            physics: {
                                enabled: false
                            }
                        });

                        // toolbar
                        document.getElementById('btn-fit').addEventListener('click', function() {
                            network.fit();
                        });
                        document.getElementById('btn-zoom-in').addEventListener('click', function() {
                            network.moveTo({
                                scale: network.getScale() * 1.2
                            });
                        });
                        document.getElementById('btn-zoom-out').addEventListener('click', function() {
                            network.moveTo({
                                scale: network.getScale() / 1.2
                            });
                        });

                        // responsive: fit on resize
                        window.addEventListener('resize', function() {
                            network.fit();
                        });
                    }

                    function buildVisData(hierarchy) {
                        var nodes = [],
                            edges = [];
                        var map = {};
                        hierarchy.forEach(function(n) {
                            map[n.id] = n;
                        });
                        hierarchy.forEach(function(n) {
                            var label = '<div style="text-align:center"><strong>' + (n.name ||
                                    'N/A') + '</strong><br><small>' + (n.title || '') +
                                '</small></div>';
                            nodes.push({
                                id: String(n.id),
                                label: label,
                                shape: 'box'
                            });
                            if (n.parent !== null && typeof n.parent !== 'undefined') {
                                edges.push({
                                    from: String(n.parent),
                                    to: String(n.id)
                                });
                            }
                        });
                        return {
                            nodes: nodes,
                            edges: edges
                        };
                    }

                    // load vis-network and start
                    if (typeof vis === 'undefined' || typeof vis.Network === 'undefined') {
                        loadScript('https://unpkg.com/vis-network@9.1.2/dist/vis-network.min.js', function(
                            err) {
                            if (err) {
                                console.error('Failed to load vis-network', err);
                                document.getElementById('tree-container').innerText =
                                    'Failed to load chart library.';
                                return;
                            }
                            var visdata = buildVisData(data);
                            initVis(visdata.nodes, visdata.edges);
                        });
                    } else {
                        var visdata = buildVisData(data);
                        initVis(visdata.nodes, visdata.edges);
                    }
                })();
</script>
@endsection
if (err) {