$(function() {
  var Needle, arc, arcEndRad, arcStartRad, barWidth, chart; 
  var chartInset, degToRad, el, endPadRad, height, margin, needle;
  var numSections, padRad, percToDeg, percToRad, percent;
  var radius, sectionIndx, sectionPerc, startPadRad, svg, totalPercent, width, _i;

  percent = .65;

  barWidth = 40;

  numSections = 3;

  sectionPerc = 1 / numSections / 2;

  padRad = 0.05;

  chartInset = 10;

  totalPercent = .75;

  el = d3.select('.chart-gauge');

  margin = {
    top: 20,
    right: 20,
    bottom: 30,
    left: 20
  };

  width = el[0][0].offsetWidth - margin.left - margin.right;

  height = width;

  radius = Math.min(width, height) / 2;

  percToDeg = function(perc) {
    return perc * 360;
  };

  percToRad = function(perc) {
    return degToRad(percToDeg(perc));
  };

  degToRad = function(deg) {
    return deg * Math.PI / 180;
  };

  svg = el.append('svg').attr('width', width + margin.left + margin.right).attr('height', height + margin.top + margin.bottom);

  chart = svg.append('g').attr('transform', "translate(" + ((width + margin.left) / 2) + ", " + ((height + margin.top) / 2) + ")");

  for (sectionIndx = _i = 1; 1 <= numSections ? _i <= numSections : _i >= numSections; sectionIndx = 1 <= numSections ? ++_i : --_i) {
    arcStartRad = percToRad(totalPercent);
    arcEndRad = arcStartRad + percToRad(sectionPerc);
    totalPercent += sectionPerc;
    startPadRad = sectionIndx === 0 ? 0 : padRad / 2;
    endPadRad = sectionIndx === numSections ? 0 : padRad / 2;
    arc = d3.svg.arc().outerRadius(radius - chartInset).innerRadius(radius - chartInset - barWidth).startAngle(arcStartRad + startPadRad).endAngle(arcEndRad - endPadRad);
    chart.append('path').attr('class', "arc chart-color" + sectionIndx).attr('d', arc);
  }

  Needle = (function() {
    function Needle(len, radius) {
      this.len = len;
      this.radius = radius;
    }

    Needle.prototype.drawOn = function(el, perc) {
      el.append('circle').attr('class', 'needle-center').attr('cx', 0).attr('cy', 0).attr('r', this.radius);
      return el.append('path').attr('class', 'needle').attr('d', this.mkCmd(perc));
    };

    Needle.prototype.animateOn = function(el, perc) {
      var self;
      self = this;
      return el.transition().delay(500).ease('elastic').duration(3000).selectAll('.needle').tween('progress', function() {
        return function(percentOfPercent) {
          var progress;
          progress = percentOfPercent * perc;
          return d3.select(this).attr('d', self.mkCmd(progress));
        };
      });
    };

    Needle.prototype.mkCmd = function(perc) {
      var centerX, centerY, leftX, leftY, rightX, rightY, thetaRad, topX, topY;
      thetaRad = percToRad(perc / 2);
      centerX = 0;
      centerY = 0;
      topX = centerX - this.len * Math.cos(thetaRad);
      topY = centerY - this.len * Math.sin(thetaRad);
      leftX = centerX - this.radius * Math.cos(thetaRad - Math.PI / 2);
      leftY = centerY - this.radius * Math.sin(thetaRad - Math.PI / 2);
      rightX = centerX - this.radius * Math.cos(thetaRad + Math.PI / 2);
      rightY = centerY - this.radius * Math.sin(thetaRad + Math.PI / 2);
      return "M " + leftX + " " + leftY + " L " + topX + " " + topY + " L " + rightX + " " + rightY;
    };

    return Needle;

  })();

  needle = new Needle(90, 15);
  needle.drawOn(chart, 0);
  needle.animateOn(chart, 15);

});

