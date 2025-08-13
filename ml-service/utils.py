import cv2
import numpy as np
from PIL import Image
import base64
import io

def preprocess_image(image_path, target_size=(640, 640)):
    """
    Preprocess gambar untuk YOLO
    """
    image = cv2.imread(image_path)
    image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    
    # Resize image
    image = cv2.resize(image, target_size)
    
    return image

def draw_bounding_boxes(image, detections):
    """
    Gambar bounding boxes pada gambar
    """
    for detection in detections:
        bbox = detection['bbox']
        class_name = detection['class']
        confidence = detection['confidence']
        
        # Koordinat bounding box
        x1, y1, x2, y2 = bbox['x1'], bbox['y1'], bbox['x2'], bbox['y2']
        
        # Warna untuk setiap class
        colors = {
            'orangutan': (255, 165, 0),  # Orange
            'wild_boar': (255, 0, 0)     # Red
        }
        color = colors.get(class_name, (0, 255, 0))
        
        # Gambar rectangle
        cv2.rectangle(image, (x1, y1), (x2, y2), color, 2)
        
        # Label text
        label = f"{class_name}: {confidence:.2f}"
        label_size = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 2)[0]
        
        # Background untuk text
        cv2.rectangle(image, (x1, y1 - label_size[1] - 10), 
                     (x1 + label_size[0], y1), color, -1)
        
        # Text
        cv2.putText(image, label, (x1, y1 - 5), 
                   cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 2)
    
    return image

def image_to_base64(image):
    """
    Convert OpenCV image ke base64 string
    """
    _, buffer = cv2.imencode('.jpg', image)
    img_base64 = base64.b64encode(buffer).decode('utf-8')
    return img_base64

def base64_to_image(base64_string):
    """
    Convert base64 string ke OpenCV image
    """
    img_data = base64.b64decode(base64_string)
    nparr = np.frombuffer(img_data, np.uint8)
    image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    return image

def get_detection_summary(detections):
    """
    Buat ringkasan hasil deteksi
    """
    summary = {
        'total': len(detections),
        'orangutan_count': 0,
        'wild_boar_count': 0,
        'highest_confidence': 0,
        'animals_detected': []
    }
    
    for detection in detections:
        class_name = detection['class']
        confidence = detection['confidence']
        
        if class_name == 'orangutan':
            summary['orangutan_count'] += 1
        elif class_name == 'wild_boar':
            summary['wild_boar_count'] += 1
        
        if confidence > summary['highest_confidence']:
            summary['highest_confidence'] = confidence
        
        if class_name not in summary['animals_detected']:
            summary['animals_detected'].append(class_name)
    
    return summary