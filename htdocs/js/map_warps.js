// Larger scale => smaller graph in the svg viewBox
var scale = 1.1;
var width = scale * window.innerWidth;
var height = scale * window.innerHeight;

// Set the color cycle for the nodes
var color = d3.scaleOrdinal(d3.schemeCategory10);

var svg = d3.select("body")
	.append("svg")
		.attr("preserveAspectRatio", "xMinYMin meet")
		.attr("viewBox", [0, 0, width, height])
		.classed("svg-content", true)
		// Enable zoom and pan
		.call(d3.zoom().on("zoom", function () {
			$("g.nodes").attr("transform", d3.event.transform);
			$("g.links").attr("transform", d3.event.transform);
		}));

// Load the link data into the svg
var link = svg.append("g")
	.attr("class", "links")
	.selectAll("line")
		.data(graph.links)
		.enter().append("line")
			.attr("stroke", "#aaa");

// Load the node data into the svg
var node = svg.append("g")
	.attr("class", "nodes")
	.selectAll("g")
		.data(graph.nodes)
		.enter().append("g");

var circles = node.append("a")
	.attr("xlink:href", function(d) { return "map_galaxy.php?galaxy_id=" + d.id; })
	.append("circle")
		.attr("r", function(d) { return Math.sqrt(d.size); })
		.attr("fill", function(d) { return color(d.group); })
		.call(d3.drag()
			.on("start", dragstarted)
			.on("drag", dragged)
			.on("end", dragended));

var labels = node.append("text")
	.text(function(d) { return d.name; })
	.attr("font-family", "sans-serif")
	.style("fill", "white");

var simulation = d3.forceSimulation()
	.force("link", d3.forceLink()
		.distance(100)
		.id(function(d) { return d.name; }))
	.force("charge", d3.forceManyBody().strength(-500))
	.force("center", d3.forceCenter(width / 2, height / 2));

// Load the node data into the simulation
simulation
	.nodes(graph.nodes);

// Load the link data into the simulation
simulation.force("link")
	.links(graph.links);

// Set the listener for tick events
simulation.on("tick", function () {
	link
		.attr("x1", function(d) { return d.source.x; })
		.attr("y1", function(d) { return d.source.y; })
		.attr("x2", function(d) { return d.target.x; })
		.attr("y2", function(d) { return d.target.y; });

	// Ideally we would modify the "transform" attribute of the node
	// variable using "translate", but this creates jittery movement
	// (probably a browser bug), so we instead reposition the circles
	// and labels individually.
	circles
		.attr("cx", function(d) { return d.x; })
		.attr("cy", function(d) { return d.y; });

	labels
		.attr("x", function(d) { return d.x + 10; })
		.attr("y", function(d) { return d.y + 4; });
});

function dragstarted(d) {
	if (!d3.event.active) {
		simulation.alphaTarget(0.3).restart();
	}
	d.fx = d.x;
	d.fy = d.y;
}

function dragged(d) {
	d.fx = d3.event.x;
	d.fy = d3.event.y;
}

function dragended(d) {
	if (!d3.event.active) {
		simulation.alphaTarget(0);
	}
	d.fx = null;
	d.fy = null;
}
