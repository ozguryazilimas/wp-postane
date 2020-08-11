<?php

interface WAPT_ImageSearch {

    /**
     * @param string $query
     * @param int $page
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function search( $query, $page );

}
