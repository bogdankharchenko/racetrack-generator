Simple 2D Race Track Generator

### Requirement 
ImageMagick

### Usage Examples
```
$track = (new RaceTrack())->drawTrack();
        
header("Content-Type: image/png");
echo $track->getImageBlob();
```

Additional Options
```php
$track = (new Track())
          ->setBackgroundColor('black')
          ->setStrokeColor('white')
          ->setStrokeWidth(2)
          ->setStrokeOpacity(0.8)
          ->drawTrack(); //returns an Imagick Object
          

$track->setImageFormat("png");
$track->trimImage(0);
	
//square up an image
$track->rotateImage('black', 45);
$track->scaleImage(300, 300, true);
	
header("Content-Type: image/png");
echo $track->getImageBlob();
```

### Credit
This is a port of a JavaScript Racetrack generator found on,
http://static.opengameart.org/procgen/track.html
https://opengameart.org/forumtopic/procedural-racetrack-generation-in-javascript


