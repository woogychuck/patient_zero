<?php require_once 'templates/header.php' ?>
<svg class="chart"></svg>

<script>
    var root;

    // ************** Generate the tree diagram	 *****************
    var margin = {top: 20, right: 120, bottom: 20, left: 120},
            width = 960 - margin.right - margin.left,
            height = 500 - margin.top - margin.bottom;

    var i = 0;

    var tree = d3.layout.tree().size([height, width]);

    var diagonal = d3.svg.diagonal().projection(function(d) { return [d.y, d.x]; });

    var svg = d3.select(".chart")
            .attr("width", width + margin.right + margin.left)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


    d3.json("data/search_results_sdcc.json", function(json) {
        root = json;
        update();
    });

    function update() {

        // Compute the new tree layout.
        var nodes = tree.nodes(root).reverse();
        var links = tree.links(nodes);

        // Normalize for fixed-depth.
        nodes.forEach(function(d) { d.y = d.depth * 180; });

        // Declare the nodes…
        var node = svg.selectAll("g.node")
                .data(nodes, function(d) { return d.id || (d.id = ++i); });

        // Enter the nodes.
        var nodeEnter = node.enter().append("g")
                .attr("class", "node")
                .attr("transform", function(d) {
                    return "translate(" + d.y + "," + d.x + ")"; });

        nodeEnter.append("circle")
                .attr("r", function(d) { return d.weight/100; })
                .style("fill", function(d) { return 'steelblue'; });

        nodeEnter.append('text')
                .attr("x", function(d) {
                    return d.children || d._children ?
                            (d.value + 4) * -1 : d.value + 4 })
                .attr("dy", ".35em")
                .attr("text-anchor", function(d) {
                    return d.children || d._children ? "end" : "start"; })
                .text(function(d){ return d.name;})


        // Declare the links…
        var link = svg.selectAll("path.link")
                .data(links, function(d) { return d.target.id; });

        // Enter the links.
        link.enter().insert("path", "g")
                .attr("class", "link")
                .style("stroke", function(d) { return '#666'; })
                .attr("d", diagonal);

    }
</script>

<?php require_once 'templates/footer.php' ?>