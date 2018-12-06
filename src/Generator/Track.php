<?php

namespace Amberlampsio\Generator;

/*
 * Ported from http://static.opengameart.org/procgen/track.html
 * http://static.opengameart.org/procgen/procgen_track.js
 */
class Track
{
	public $track;
	
	public $curves;
	
	protected $background_color = 'transparent';
	
	protected $stroke_color = 'black';
	
	protected $stroke_width = 5;
	
	protected $stroke_opacity = 1;
	
	protected $fill_color = 'transparent';
	
	protected $min_points;
	
	protected $max_points;
	
	protected $min_segment_length;
	
	protected $max_segment_length;
	
	protected $curve;
	
	protected $maxAngle;
	
	public function __construct($min_points = 40, $max_points = 50, $min_segment_length = 2, $max_segment_length = 16, $curve = 0.3, $maxAngle = 90)
	{
		$this->min_points = $min_points;
		$this->max_points = $max_points;
		$this->min_segment_length = $min_segment_length;
		$this->max_segment_length = $max_segment_length;
		$this->curve = $curve;
		$this->maxAngle = $maxAngle / 360 * pi();
		$this->track = (object) [
			'data' => [
				[
					'x' => 450,
					'y' => 450,
				],
			],
			'points' => rand($min_points, $max_points) + $max_points,
			'minX' => 0,
			'minY' => 0,
			'maxX' => 0,
			'maxY' => 0,
			'minSize' => 0,
			'maxSize' => 0,
		];
	}
	
	/**
	 * @param string $color
	 * @return Track
	 */
	public function setBackgroundColor($color)
	{
		$this->background_color = $color;
		return $this;
	}
	
	/**
	 * @param string $color
	 * @return Track
	 */
	public function setStrokeColor($color)
	{
		$this->stroke_color = $color;
		return $this;
	}
	
	/**
	 * @param $width
	 * @return Track
	 */
	public function setStrokeWidth($width)
	{
		$this->stroke_width = $width;
		return $this;
	}
	
	/**
	 * @param $opacity
	 * @return Track
	 */
	public function setStrokeOpacity($opacity)
	{
		$this->stroke_opacity = $opacity;
		return $this;
	}
	
	/**
	 * @param $color
	 * @return Track
	 */
	public function setFillColor($color)
	{
		$this->fill_color = $color;
		return $this;
	}
	
	/**
	 * @return \Imagick
	 */
	public function drawTrack()
	{
		return $this
			->generateCurves()
			->drawCurves()
			->drawCurvesOnImage();
	}
	
	/**
	 * Generate points of track
	 *
	 * @return Track
	 */
	public function generateCurves()
	{
		$direction = 0;
		
		for ($i = 1; $i < $this->track->points; $i++) {
			
			$len = rand($this->min_segment_length, $this->max_segment_length) + $this->min_segment_length;
			
			$direction_x = sin($direction) * $len;
			$direction_y = cos($direction) * $len;
			
			$x = $this->track->data[$i - 1]['x'] + $direction_x;
			$y = $this->track->data[$i - 1]['y'] + $direction_y;
			
			$this->track->data[$i] = [
				'x' => $x,
				'y' => $y,
			];
			
			$rand = mt_rand() / mt_getrandmax();
			$turn = pow($rand, 1 / $this->curve);
			if (mt_rand() / mt_getrandmax() < .25) {
				$turn = -$turn;
			}
			$direction += $turn * $this->maxAngle;
		}
		
		$q = floor($this->track->points * 0.75);
		$c = $this->track->points - $q;
		
		$x0 = $this->track->data[0]['x'];
		$y0 = $this->track->data[0]['y'];
		
		for ($i = $q; $i < $this->track->points; $i++) {
			$x = $this->track->data[$i]['x'];
			$y = $this->track->data[$i]['y'];
			$a = $i - $q;
			
			$this->track->data[$i]['x'] = $x0 * $a / $c + $x * (1 - $a / $c);
			$this->track->data[$i]['y'] = $y0 * $a / $c + $y * (1 - $a / $c);
		}
		
		for ($i = 1; $i < $this->track->points; $i++) {
			
			$x = $this->track->data[$i]['x'];
			$y = $this->track->data[$i]['y'];
			
			if ($x < $this->track->minX) {
				$this->track->minX = $x;
			}
			if ($y < $this->track->minY) {
				$this->track->minY = $y;
			}
			
			if ($x > $this->track->maxX) {
				$this->track->maxX = $x;
			}
			if ($y > $this->track->maxY) {
				$this->track->maxY = $y;
			}
			
			$this->track->minSize = min($this->track->minX, $this->track->minY);
			$this->track->maxSize = min($this->track->maxX, $this->track->maxY);
		}
		
		return $this;
	}
	
	/**
	 * @return Track
	 */
	public function drawCurves()
	{
		$this->curves = new \ImagickDraw();
		
		$this->curves->setStrokeOpacity($this->stroke_opacity);
		$this->curves->setStrokeWidth($this->stroke_width);
		$this->curves->setStrokeColor($this->stroke_color);
		$this->curves->setFillColor($this->fill_color);
		
		$this->curves->pathStart();
		for ($i = 0; $i <= $this->track->points; $i++) {
			$p1 = $i % $this->track->points;
			$p2 = ($i + 1) % $this->track->points;
			$x = ($this->track->data[$p1]['x'] + $this->track->data[$p2]['x']) / 2;
			$y = ($this->track->data[$p1]['y'] + $this->track->data[$p2]['y']) / 2;
			
			if ($i == 0) {
				$this->curves->pathMoveToAbsolute($x, $y);
			} else {
				$this->curves->pathCurveToQuadraticBezierAbsolute(
					$this->track->data[$p1]['x'], $this->track->data[$p1]['y'], $x, $y
				);
			}
		}
		
		$this->curves->pathFinish();
		
		return $this;
	}
	
	/**
	 * @return \Imagick
	 */
	public function drawCurvesOnImage()
	{
		$image = new \Imagick();
		
		//extra padding to prevent off screen bleed
		$image->newImage($this->track->maxX + 300, $this->track->maxY + 300, $this->background_color);
		
		$image->drawImage($this->curves);
		
		return $image;
	}
}
