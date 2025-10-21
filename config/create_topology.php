<?php

return [
    "fnc_set_insert_road_and_create_topology" => " 
        CREATE OR REPLACE FUNCTION fnc_set_insert_road_and_create_topology()
        RETURNS void LANGUAGE plpgsql
        AS $$
        BEGIN
            CREATE TABLE IF NOT EXISTS utility_info.roads_network_noded (
                id BIGSERIAL PRIMARY KEY,
                old_id INTEGER,
                sub_id INTEGER,
                source BIGINT,
                target BIGINT,
                the_geom GEOMETRY(LineString, 4326),
                distance DOUBLE PRECISION
            ) TABLESPACE pg_default;

            INSERT INTO utility_info.roads_network_noded (the_geom)
            SELECT (ST_Dump(geom)).geom
            FROM utility_info.roads;

            PERFORM pgr_createTopology(
                'utility_info.roads_network_noded',
                0.00001,
                'the_geom',
                'id'
            );

            PERFORM pgr_nodeNetwork(
                'utility_info.roads_network_noded',
                0.00001,
                the_geom := 'the_geom'
            );

            DROP TABLE IF EXISTS utility_info.roads_network_noded;

            ALTER TABLE utility_info.roads_network_noded_noded
            RENAME TO roads_network_noded;

            ALTER INDEX utility_info.roads_network_noded_noded_the_geom_idx
            RENAME TO roads_network_noded_the_geom_idx;

            PERFORM pgr_createTopology(
                'utility_info.roads_network_noded',
                0.00001,
                'the_geom',
                'id'
            );

            ALTER TABLE utility_info.roads_network_noded
            ADD COLUMN IF NOT EXISTS distance DOUBLE PRECISION;

            UPDATE utility_info.roads_network_noded
            SET distance = ST_Length(the_geom::geography);
        END;
        $$;
    ",

    "tgr_set_insert_road_and_create_topology" => "
        DROP TRIGGER IF EXISTS tgr_set_insert_road_and_create_topology ON utility_info.roads;
        CREATE TRIGGER tgr_set_insert_road_and_create_topology
        AFTER INSERT OR DELETE OR UPDATE
        ON utility_info.roads
        FOR EACH ROW
        EXECUTE FUNCTION fnc_set_insert_road_and_create_topology();
    "
];
