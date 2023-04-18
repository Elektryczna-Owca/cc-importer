
ALTER TABLE curriculum_has_resource
    DROP FOREIGN KEY fk_curriculum_has_resource_resource1;
ALTER TABLE curriculum_has_resource
    ADD CONSTRAINT fk_curriculum_has_resource_resource1
        FOREIGN KEY (resource_id) 
        REFERENCES resource (id)
        ON DELETE CASCADE;
		
		


CREATE DEFINER=`root`@`localhost` PROCEDURE `IMPORT_RESOURCE`(
	IN R_URL VARCHAR(1024) CHARSET utf8mb4 COLLATE utf8mb4_polish_ci, 
	IN R_COMMENT TEXT, 
    IN R_IMPORTER_ID INT, 
	IN C_SUBJECT VARCHAR(45) CHARSET utf8mb4 COLLATE utf8mb4_polish_ci,
	IN C_GRADE0 TINYINT, 
    IN C_GRADE1 TINYINT, 
    IN C_GRADE2 TINYINT, 
    IN C_GRADE3 TINYINT, 
    IN C_GRADE4 TINYINT, 
    IN C_GRADE5 TINYINT, 
    IN C_GRADE6 TINYINT, 
    IN C_GRADE7 TINYINT, 
    IN C_GRADE8 TINYINT, 
    IN C_SYMBOLS TEXT CHARSET utf8mb4 COLLATE utf8mb4_polish_ci,
    OUT O_COUNT INT,
    OUT O_ERROR VARCHAR(1024))
BEGIN
	DECLARE R_ID INT;
	SELECT COUNT(*) INTO R_ID FROM curriculum.resource r WHERE r.url = R_URL;
   
	IF (R_ID = 0) THEN
		SELECT COUNT(*) INTO O_COUNT FROM curriculum c WHERE c.subject = C_SUBJECT 
		AND FIND_IN_SET(c.symbol, C_SYMBOLS)
		AND (
		c.grade0 = IF (C_GRADE0 = 1, 1, -1)
		OR c.grade1 = IF (C_GRADE1 = 1, 1, -1)
		OR c.grade2 = IF (C_GRADE2 = 1, 1, -1)
		OR c.grade3 = IF (C_GRADE3 = 1, 1, -1)
		OR c.grade4 = IF (C_GRADE4 = 1, 1, -1)
		OR c.grade5 = IF (C_GRADE5 = 1, 1, -1)
		OR c.grade6 = IF (C_GRADE6 = 1, 1, -1)
		OR c.grade7 = IF (C_GRADE7 = 1, 1, -1)
		OR c.grade8 = IF (C_GRADE8 = 1, 1, -1));  
		
		IF (O_COUNT > 0) THEN
			INSERT INTO curriculum.resource(importer_id, url, comment) values(R_IMPORTER_ID, R_URL, R_COMMENT);
			SELECT LAST_INSERT_ID() INTO R_ID;
			INSERT INTO curriculum_has_resource(curriculum_id, resource_id)
				SELECT id, R_ID  FROM curriculum c WHERE c.subject = C_SUBJECT 
				AND FIND_IN_SET(c.symbol, C_SYMBOLS)
				AND (
				c.grade0 = IF (C_GRADE0 = 1, 1, -1)
				OR c.grade1 = IF (C_GRADE1 = 1, 1, -1)
				OR c.grade2 = IF (C_GRADE2 = 1, 1, -1)
				OR c.grade3 = IF (C_GRADE3 = 1, 1, -1)
				OR c.grade4 = IF (C_GRADE4 = 1, 1, -1)
				OR c.grade5 = IF (C_GRADE5 = 1, 1, -1)
				OR c.grade6 = IF (C_GRADE6 = 1, 1, -1)
				OR c.grade7 = IF (C_GRADE7 = 1, 1, -1)
				OR c.grade8 = IF (C_GRADE8 = 1, 1, -1)); 
                
            SET O_ERROR = '';
		ELSE
			SET O_ERROR = 'No matching curriculum found';
		END IF;
	ELSE
		SET O_COUNT = 0;
		SET O_ERROR = 'URL already exits';        
	END IF;
    select O_COUNT , O_ERROR;
END