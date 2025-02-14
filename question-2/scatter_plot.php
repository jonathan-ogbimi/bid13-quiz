<?php

/*
What I make of the scatter point data:
1) The data set contains 6,377 points
2) X-axis ranges from 147 to 837 (range of 690 units)
3) Y-axis ranges from -946 to -48 (range of 898 units)
4) All Y values are negative
5) The X values are all positive


*/
class ScatterPlot {
    private $width = 800;
    private $height = 600;
    private $padding = 50;
    private $data = [];
    private $image;
    private $minX;
    private $maxX;
    private $minY;
    private $maxY;

    public function __construct(string $csvFile) {
        $this->loadData($csvFile);
        $this->calculateBounds();
        $this->initializeImage();
    }

    private function loadData(string $csvFile): void {
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) >= 2) {
                    $this->data[] = [
                        'x' => (float)$row[0],
                        'y' => (float)$row[1]
                    ];
                }
            }
            fclose($handle);
        } else {
            throw new Exception("Could not open CSV file");
        }
    }

    private function calculateBounds(): void {
        $this->minX = min(array_column($this->data, 'x'));
        $this->maxX = max(array_column($this->data, 'x'));
        $this->minY = min(array_column($this->data, 'y'));
        $this->maxY = max(array_column($this->data, 'y'));
    }

    private function initializeImage(): void {
        $this->image = imagecreatetruecolor($this->width, $this->height);
        
        // Set background to white
        $white = imagecolorallocate($this->image, 255, 255, 255);
        imagefill($this->image, 0, 0, $white);
        
        // Draw axes
        $black = imagecolorallocate($this->image, 0, 0, 0);
        $gray = imagecolorallocate($this->image, 200, 200, 200);
        
        // Draw grid lines
        $this->drawGrid($gray);
        
        // Draw axes
        imageline($this->image, $this->padding, $this->height - $this->padding, 
                 $this->width - $this->padding, $this->height - $this->padding, $black); // X axis
        imageline($this->image, $this->padding, $this->padding, 
                 $this->padding, $this->height - $this->padding, $black); // Y axis
        
        // Draw axis labels
        $this->drawAxisLabels($black);
    }

    private function drawGrid($color): void {
        // Draw vertical grid lines
        for ($i = 0; $i <= 10; $i++) {
            $x = $this->padding + ($i * ($this->width - 2 * $this->padding) / 10);
            imageline($this->image, $x, $this->padding, $x, $this->height - $this->padding, $color);
        }
        
        // Draw horizontal grid lines
        for ($i = 0; $i <= 10; $i++) {
            $y = $this->padding + ($i * ($this->height - 2 * $this->padding) / 10);
            imageline($this->image, $this->padding, $y, $this->width - $this->padding, $y, $color);
        }
    }

    private function drawAxisLabels($color): void {
        // X axis labels
        for ($i = 0; $i <= 10; $i++) {
            $value = $this->minX + ($i * ($this->maxX - $this->minX) / 10);
            $x = $this->padding + ($i * ($this->width - 2 * $this->padding) / 10);
            $label = number_format($value, 0);
            imagestring($this->image, 2, $x - 10, $this->height - $this->padding + 5, $label, $color);
        }
        
        // Y axis labels
        for ($i = 0; $i <= 10; $i++) {
            $value = $this->maxY - ($i * ($this->maxY - $this->minY) / 10);
            $y = $this->padding + ($i * ($this->height - 2 * $this->padding) / 10);
            $label = number_format($value, 0);
            imagestring($this->image, 2, 5, $y - 7, $label, $color);
        }
    }

    public function plot(): void {
        // Plot points
        $blue = imagecolorallocate($this->image, 0, 0, 255);
        
        foreach ($this->data as $point) {
            $x = $this->padding + (($point['x'] - $this->minX) * ($this->width - 2 * $this->padding) / ($this->maxX - $this->minX));
            $y = $this->padding + (($this->maxY - $point['y']) * ($this->height - 2 * $this->padding) / ($this->maxY - $this->minY));
            
            // Draw point as a small circle
            imagefilledellipse($this->image, (int)$x, (int)$y, 4, 4, $blue);
        }
    }

    public function save(string $filename): void {
        imagepng($this->image, $filename);
        imagedestroy($this->image);
    }
}

// Usage
try {
    $plot = new ScatterPlot('scatter_plot.csv');
    $plot->plot();
    $plot->save('scatter_plot.png');
    echo "Scatter plot has been generated as scatter_plot.png";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}