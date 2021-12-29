<p>The plotted course is <?php echo $Path->getLength() . ' ' . pluralise('sector', $Path->getLength()); ?> long and costs <?php echo $Path->getTurns() . ' ' . pluralise('turn', $Path->getTurns()); ?> to traverse.</p>

<br />
<h2>Plotted Course</h2>
<p><?php echo $FullPath; ?></p>
