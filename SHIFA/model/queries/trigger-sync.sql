DELIMITER //
CREATE TRIGGER after_pharmacy_update
AFTER UPDATE ON pharmacy
FOR EACH ROW
BEGIN
  -- Trigger if any monitored field changes
  IF OLD.pharmacy_name != NEW.pharmacy_name OR
     OLD.phone_number != NEW.phone_number OR
     OLD.email != NEW.email OR
     OLD.address != NEW.address THEN
    
    -- Just send the ID
    SET @cmd = CONCAT(
      'curl -X POST http://localhost/SHIFA/web-trigger/src/PharmacySync.php',
       ' -H "Authorization: Bearer r?:W$DWX9s1~16BYGh.TuCax2luS$xI%YZIla~06c" ',
      ' -d "pharmacy_id=', NEW.pharmacy_id, '"'
    );
    SET @result = sys_exec(@cmd);
  END IF;
END//
DELIMITER ;
