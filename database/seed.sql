USE car_spare_parts_db;

-- SAMPLE SPARE PARTS
INSERT INTO parts (name, category, price, stock, brand)
VALUES
('Engine Oil Filter', 'Filters', 15000, 25, 'Toyota'),
('Brake Pads', 'Braking System', 45000, 15, 'Bosch'),
('Car Battery', 'Electrical', 180000, 10, 'Amaron'),
('Air Filter', 'Filters', 20000, 30, 'Mann'),
('Spark Plug', 'Engine Parts', 12000, 40, 'NGK'),
('Shock Absorber', 'Suspension', 95000, 12, 'Monroe'),
('Alternator', 'Electrical', 220000, 8, 'Denso'),
('Clutch Plate', 'Transmission', 110000, 10, 'Exedy');