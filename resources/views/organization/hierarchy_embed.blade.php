<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Chart</title>
    <style>
        /* Basic reset */
        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            /* Allow scrolling within the iframe */
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            /* Light background for the whole page */
        }

        /* Chart styles */
        /* Removed old conflicting styles */

        .google-visualization-orgchart-node {
            padding: 0;
            border: none !important;
            /* Remove default border */
            background: transparent !important;
            /* Remove default background */
            box-shadow: none !important;
            /* Remove default shadow */
        }

        /* Common Card Styles */
        .orgcard {
            background: #ffffff;
            padding: 20px 15px;
            width: 140px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .orgcard:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: #e0e0e0;
        }

        /* Profile Picture */
        .orgcard-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
            border: 3px solid #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Typography */
        .orgcard .title {
            font-weight: 700;
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 4px;
            line-height: 1.3;
            text-align: center;
        }

        .orgcard-title {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 500;
            margin-bottom: 4px;
            text-align: center;
        }

        .orgcard-dept {
            font-size: 11px;
            color: #3498db;
            background-color: rgba(52, 152, 219, 0.1);
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
            display: inline-block;
            margin-top: 4px;
        }

        /* Department Card Specifics */
        .department-card {
            width: 200px;
            padding: 12px 15px;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-left: 5px solid #27ae60;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            text-align: left;
            gap: 12px;
        }

        .department-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 6px rgba(39, 174, 96, 0.2);
        }

        .department-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .department-card .title {
            font-size: 15px;
            color: #2c3e50;
            margin-bottom: 2px;
            text-align: left;
        }

        .department-card .subtitle {
            font-size: 11px;
            color: #95a5a6;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Dealership Card Specifics */
        .dealership-card {
            width: 220px;
            padding: 15px 20px;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-left: 5px solid #3498db;
            display: flex;
            flex-direction: row;
            /* Horizontal layout for dealership */
            align-items: center;
            justify-content: flex-start;
            text-align: left;
            gap: 15px;
        }

        .dealership-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.2);
        }

        .dealership-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .dealership-card .title {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 2px;
            text-align: left;
        }

        .dealership-card .subtitle {
            font-size: 12px;
            color: #95a5a6;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Connector Lines (Google Charts overrides) */
        .google-visualization-orgchart-linebottom {
            border-color: #bdc3c7 !important;
            border-width: 2px !important;
        }

        #chart-container {
            width: 100%;
            height: 100vh;
            overflow: auto;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            position: relative;
            cursor: grab;
            /* specific cursor for draggable area */
            user-select: none;
            /* prevent text selection while dragging */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE 10+ */
        }

        #chart-container::-webkit-scrollbar {
            display: none;
            /* Chrome/Safari/Webkit */
        }

        #chart-container.grabbing {
            cursor: grabbing;
        }

        #orgchart-google {
            transform-origin: top left;
            transition: transform 0.2s ease-out;
            /* Faster transition for responsiveness */
            padding: 20px;
            background-color: #e3f2fd;
            display: inline-block;
            min-height: 200px;
            pointer-events: none;
            /* Let clicks pass through to container for dragging */
        }

        /* Re-enable pointer events for nodes so they can be clicked/hovered */
        .google-visualization-orgchart-node {
            pointer-events: auto;
        }

        #zoom-controls {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: none;
            flex-direction: column;
            /* Stack vertically for better mobile/desktop feel */
            gap: 10px;
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .zoom-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #333;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .zoom-btn:hover {
            background-color: #3498db;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .zoom-btn svg {
            width: 20px;
            height: 20px;
        }

        /* Connector Lines Styling */
        .google-visualization-orgchart-lineleft {
            border-left: 2px solid #999 !important;
        }

        .google-visualization-orgchart-lineright {
            border-right: 2px solid #999 !important;
        }

        .google-visualization-orgchart-linebottom {
            border-bottom: 2px solid #999 !important;
        }

        .google-visualization-orgchart-connrow-medium {
            height: 20px !important;
        }

        /* Remove default border/background from Google's table cells to clean up */
        .google-visualization-orgchart-table {
            border-collapse: collapse !important;
        }

        /* Remove padding from the cell containing the node to remove gaps */
        .google-visualization-orgchart-node {
            padding: 0 !important;
            border: none !important;
        }

        /* Ensure nodes sit on top of lines cleanly and touch them */
        .node-card {
            position: relative;
            z-index: 2;
            margin: 5px auto !important;
            /* Center horizontally, small vertical gap */
            /* Remove large margins that hide lines */
        }

        /* Specific fix: Pull the node up slightly to cover the top line gap */
        .google-visualization-orgchart-node>.node-card {
            margin-top: -2px !important;
        }

        /* Context Menu */
        #context-menu {
            display: none;
            position: absolute;
            z-index: 10000;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            width: 180px;
            overflow: hidden;
            border: 1px solid #eee;
        }

        #context-menu ul {
            list-style: none;
            padding: 5px 0;
            margin: 0;
        }

        #context-menu ul li {
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.1s;
        }

        #context-menu ul li:hover {
            background-color: #f8f9fa;
        }

        #context-menu ul li svg {
            width: 16px;
            height: 16px;
            color: #7f8c8d;
        }
    </style>
</head>

<body>
    <div id="chart-container">
        <div id="orgchart-google">Loading chart...</div>
    </div>

    <!-- Custom Context Menu -->
    <div id="context-menu">
        <ul>
            <li id="menu-view">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                View Employee
            </li>
            <li id="menu-edit">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit Employee
            </li>
        </ul>
    </div>

    <div id="zoom-controls">
        <button class="zoom-btn" onclick="zoomIn()" title="Zoom In">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>
        <button class="zoom-btn" onclick="zoomOut()" title="Zoom Out">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>
        <button class="zoom-btn" onclick="fitToScreen()" title="Fit to Screen">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3" />
            </svg>
        </button>
        <button class="zoom-btn" onclick="resetZoom()" title="Reset Zoom (100%)">
            <span style="font-size: 12px; font-weight: bold;">1:1</span>
        </button>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        var currentScale = 1;
        var googleChartEl = null;
        var container = null;

        // Drag to scroll variables
        let isDown = false;
        let startX;
        let startY;
        let scrollLeft;
        let scrollTop;
        let rafId = null;

        function applyScale(scale) {
            if (scale < 0.05) scale = 0.05;
            if (scale > 3) scale = 3;
            currentScale = scale;
            if (googleChartEl) {
                googleChartEl.style.transform = `scale(${scale})`;
            }
        }

        function zoomIn() {
            applyScale(currentScale + 0.1);
        }

        function zoomOut() {
            applyScale(currentScale - 0.1);
        }

        function resetZoom() {
            applyScale(1);
        }

        function fitToScreen() {
            if (!googleChartEl) return;
            var chartTable = googleChartEl.querySelector('table');
            if (!chartTable) {
                setTimeout(fitToScreen, 500);
                return;
            }

            var contentWidth = chartTable.offsetWidth;
            var containerWidth = window.innerWidth - 40;

            if (contentWidth <= 0) return;

            var scale = containerWidth / contentWidth;

            if (scale < 0.1) scale = 0.1;
            if (scale > 1) scale = 1;

            applyScale(scale);
        }

        document.addEventListener('DOMContentLoaded', function() {
            googleChartEl = document.getElementById('orgchart-google');
            container = document.getElementById('chart-container');

            // Drag to scroll implementation
            container.addEventListener('mousedown', (e) => {
                // Don't trigger drag if clicking a button or link
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('button') || e.target.closest('a')) {
                    return;
                }

                isDown = true;
                container.classList.add('grabbing');
                startX = e.pageX - container.offsetLeft;
                startY = e.pageY - container.offsetTop;
                scrollLeft = container.scrollLeft;
                scrollTop = container.scrollTop;

                // Disable smooth scrolling during drag for instant response
                container.style.scrollBehavior = 'auto';
            });

            // Helper to stop dragging
            function stopDrag() {
                if (!isDown) return;
                isDown = false;
                container.classList.remove('grabbing');
                container.style.scrollBehavior = ''; // Restore default
                if (rafId) {
                    cancelAnimationFrame(rafId);
                    rafId = null;
                }
            }

            // Attach to document to handle dragging outside container
            document.addEventListener('mouseup', stopDrag);

            // Critical: Stop dragging if mouse leaves the iframe window
            document.addEventListener('mouseleave', stopDrag);
            window.addEventListener('blur', stopDrag);

            document.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();

                // Use requestAnimationFrame for smooth performance
                if (rafId) return;

                rafId = requestAnimationFrame(() => {
                    const x = e.pageX - container.offsetLeft;
                    const y = e.pageY - container.offsetTop;
                    const walkX = (x - startX); // 1:1 movement
                    const walkY = (y - startY);
                    container.scrollLeft = scrollLeft - walkX;
                    container.scrollTop = scrollTop - walkY;
                    rafId = null;
                });
            });

            // ... rest of initialization ...

            // ... rest of initialization ...

            // Safe data loading
            var flatData = null;
            try {
                flatData = {!!json_encode($hierarchy) !!};
            } catch (e) {
                console.error('Error parsing hierarchy data:', e);
            }



            if (!flatData || !Array.isArray(flatData) || flatData.length === 0) {
                googleChartEl.innerHTML = `
                    <div style="text-align:center; padding: 40px; color: #7f8c8d;">
                        <h3>No hierarchy data available</h3>
                        <p>Please check if the organization structure is correctly defined.</p>
                    </div>`;
                document.getElementById('zoom-controls').style.display = 'none';
                return;
            }

            // Clear loading text
            googleChartEl.innerHTML = '';

            // ... rest of code ...

            var rows = [];
            flatData.forEach(function(node) {
                var label = '';
                if (node.user_type === 'department') {
                    label = `
                        <div class='orgcard department-card'>
                            <div class='department-icon'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            </div>
                            <div class='department-info'>
                                <div class='title'>${node.name}</div>
                                <div class='subtitle'>Department</div>
                            </div>
                        </div>
                    `;
                } else if (node.user_type === 'dealership') {
                    label = `
                        <div class='orgcard dealership-card'>
                            <div class='dealership-icon'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M8 21v-4h8v4"/></svg>
                            </div>
                            <div class='dealership-info'>
                                <div class='title'>${node.name}</div>
                                <div class='subtitle'>Dealership</div>
                            </div>
                        </div>
                    `;
                } else {
                    var imgSrc = node.profile_pic ? node.profile_pic : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(node.name) + '&background=3498db&color=fff&size=128';
                    var showDept = node.department && node.department.trim() && node.department.trim().toLowerCase() !== 'null';
                    var deptHtml = showDept ? `<div class='orgcard-dept'>${node.department}</div>` : '';

                    // Add data-employee-id for context menu support
                    var empIdAttr = node.employee_id ? `data-employee-id='${node.employee_id}'` : '';

                    label = `
                        <div class='orgcard employee-node' ${empIdAttr}>
                            <img src='${imgSrc}' class='orgcard-pic' alt='${node.name}' />
                            <div class='title'>${node.name}</div>
                            <div class='orgcard-title'>${node.title}</div>
                            ${deptHtml}
                        </div>
                    `;
                }

                var parentId = node.parent ? node.parent : '';
                var tooltip = `${node.name} - ${node.title} (${node.department || 'N/A'})`;

                rows.push([{
                    v: node.id,
                    f: label
                }, parentId, tooltip]);
            });

            google.charts.load('current', {
                packages: ['orgchart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                try {
                    var data = new google.visualization.DataTable();
                    data.addColumn('string', 'Name');
                    data.addColumn('string', 'Manager');
                    data.addColumn('string', 'ToolTip');
                    data.addRows(rows);

                    var chart = new google.visualization.OrgChart(googleChartEl);

                    // Add a ready listener
                    google.visualization.events.addListener(chart, 'ready', function() {
                        // Notify parent window that chart is ready (send multiple times to ensure receipt)
                        function notifyParent() {
                            window.parent.postMessage('orgChartReady', '*');
                        }
                        notifyParent(); // Immediate
                        setTimeout(notifyParent, 100); // After 100ms
                        setTimeout(notifyParent, 500); // After 500ms

                        // Ensure visible immediately
                        googleChartEl.style.opacity = 1;

                        // Scale chart

                        applyScale(1);
                    });

                    chart.draw(data, {
                        allowHtml: true,
                        size: 'medium',
                        nodeClass: 'google-visualization-orgchart-node'
                    });

                    // Initialize Context Menu functionality
                    initContextMenu();
                } catch (err) {
                    console.error('Error drawing chart:', err);
                    googleChartEl.innerHTML = '<div style="color:red; padding:20px;">Error drawing chart: ' + err.message + '</div>';
                }
            }

            // Context Menu Logic
            var contextMenu = document.getElementById('context-menu');
            var selectedEmployeeId = null;

            function initContextMenu() {
                // Attach event listener to container to delegate to .employee-node
                document.addEventListener('contextmenu', function(e) {
                    var target = e.target.closest('.employee-node');

                    if (target) {
                        e.preventDefault();
                        var employeeId = target.getAttribute('data-employee-id');

                        if (employeeId) {
                            selectedEmployeeId = employeeId;

                            // Position menu
                            var x = e.pageX;
                            var y = e.pageY;

                            // Adjust if close to edge
                            if (x + 180 > window.innerWidth) x = window.innerWidth - 190;
                            if (y + 100 > window.innerHeight) y = window.innerHeight - 110;

                            contextMenu.style.left = x + 'px';
                            contextMenu.style.top = y + 'px';
                            contextMenu.style.display = 'block';
                        }
                    } else {
                        contextMenu.style.display = 'none';
                    }
                });

                // Hide menu on click anywhere else
                document.addEventListener('click', function(e) {
                    contextMenu.style.display = 'none';
                });

                // Hide menu on scroll 
                if (container) {
                    container.addEventListener('scroll', function() {
                        contextMenu.style.display = 'none';
                    });
                }
            }

            // Menu Item Actions
            var menuView = document.getElementById('menu-view');
            if (menuView) {
                menuView.addEventListener('click', function() {
                    if (selectedEmployeeId) {
                        window.open(`{{ route('employees.index') }}?action=view&id=${selectedEmployeeId}`, '_blank');
                    }
                });
            }

            var menuEdit = document.getElementById('menu-edit');
            if (menuEdit) {
                menuEdit.addEventListener('click', function() {
                    if (selectedEmployeeId) {
                        window.open(`{{ route('employees.index') }}?action=edit&id=${selectedEmployeeId}`, '_blank');
                    }
                });
            }
        });
    </script>
</body>

</html>