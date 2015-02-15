CREATE TABLE "user"
(
  id serial NOT NULL,
  login character varying(128) NOT NULL,
  password character varying(200),
  name character varying(200),
  email character varying(200) NOT NULL,
  CONSTRAINT user_pkey PRIMARY KEY (id),
  CONSTRAINT user_login_uniq UNIQUE (login)
);

CREATE OR REPLACE FUNCTION authenticate(IN in_login character varying, IN in_passw character varying)
  RETURNS TABLE(id integer, name character varying, email character varying) AS
$BODY$
  BEGIN
    return QUERY SELECT
      "user".id, "user".name, "user".email
    FROM
      "user"
    WHERE
      "user".login = in_login AND "user".password = md5(in_passw)
    LIMIT 1;
  END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

CREATE OR REPLACE FUNCTION get_user_by_login(IN in_login character varying)
  RETURNS TABLE(id integer, name character varying, email character varying) AS
$BODY$
  BEGIN
    return QUERY SELECT
      "user".id, "user".name, "user".email
    FROM
      "user"
    WHERE
      "user".login = in_login
    LIMIT 1;
  END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

CREATE OR REPLACE FUNCTION registered(in_login character varying, in_passw character varying, in_name character varying, in_email character varying)
  RETURNS boolean AS
$BODY$
  DECLARE
    result BOOLEAN := TRUE;
  BEGIN
    INSERT INTO "user"
        (login, password, name, email)
      values
        (in_login, md5(in_passw), in_name, in_email)
    ;
    return result;
  END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

CREATE OR REPLACE FUNCTION test_db(IN str1 character varying, IN str2 character varying)
  RETURNS SETOF boolean AS
$BODY$
  BEGIN
    return QUERY SELECT true WHERE str1 = str2;
  END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;
