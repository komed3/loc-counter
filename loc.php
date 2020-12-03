<?php
    
    error_reporting( 0 );
    
    $binary = new finfo( FILEINFO_MIME_ENCODING );
    
    $counter = [];
    
    function loc_counter( $dir ) {
        
        global $binary, $counter;
        
        $dir = rtrim( str_replace( '\\', '/', $dir ), '/' );
        
        foreach( scandir( $dir ) as $file ) {
            
            if( in_array( $file, [ '.', '..' ] ) )
                continue;
            
            $path = $dir . '/' . $file;
            
            if( is_dir( $path ) && substr( $file, 0, 1 ) != '.' )
                loc_counter( $path );
            
            if( is_file( $path ) ) {
                
                if( $binary->file( $path ) == 'binary' ) {
                    
                    $counter['binary']['files']++;
                    
                    $counter['binary']['all'] = 0;
                    $counter['binary']['loc'] = 0;
                    $counter['binary']['empty'] = 0;
                    
                } else {
                    
                    $info = pathinfo( $path );
                    
                    $handle = fopen( $path, 'r' );
                    $loc = [ 0, 0 ];
                    
                    while( !feof( $handle ) ) {
                        
                        $loc[ intval( strlen( trim( fgets( $handle ) ) ) > 0 ) ]++;
                        
                    }
                    
                    fclose( $handle );
                    
                    $counter[ $info['extension'] ]['files']++;
                    
                    $counter[ $info['extension'] ]['all'] += array_sum( $loc );
                    $counter[ $info['extension'] ]['loc'] += $loc[1];
                    $counter[ $info['extension'] ]['empty'] += $loc[0];
                    
                }
                
            }
            
        }
        
    }
    
    function loc_output( $order = 'all' ) {
        
        global $counter;
        
        $output = [
            '  FILETYPE                 FILES       ALL       LOC     EMPTY       PCT',
            '  ----------------------------------------------------------------------'
        ];
        
        $summary = [ 'files' => 0, 'all' => 0, 'loc' => 0, 'empty' => 0 ];
        
        uasort( $counter, function( $a, $b ) use ( $order ) {
            
            return $b[ $order ] <=> $a[ $order ];
            
        } );
        
        foreach( $counter as $filetype => $params ) {
            
            $summary['files'] += $params['files'];
            $summary['all'] += $params['all'];
            $summary['loc'] += $params['loc'];
            $summary['empty'] += $params['empty'];
            
            $output[] = '  ' . str_pad( strtoupper( $filetype ), 20, ' ', STR_PAD_RIGHT ) .
                str_pad( $params['files'], 10, ' ', STR_PAD_LEFT ) .
                str_pad( $params['all'], 10, ' ', STR_PAD_LEFT ) .
                str_pad( $params['loc'], 10, ' ', STR_PAD_LEFT ) .
                str_pad( $params['empty'], 10, ' ', STR_PAD_LEFT ) .
                str_pad( number_format( $params['loc'] / max( 1, $params['all'] ) * 100, 1 ), 10, ' ', STR_PAD_LEFT );
            
        }
        
        $output[] = '  ----------------------------------------------------------------------';
        $output[] = '  SUMMARY             ' .
            str_pad( $summary['files'], 10, ' ', STR_PAD_LEFT ) .
            str_pad( $summary['all'], 10, ' ', STR_PAD_LEFT ) .
            str_pad( $summary['loc'], 10, ' ', STR_PAD_LEFT ) .
            str_pad( $summary['empty'], 10, ' ', STR_PAD_LEFT ) .
            str_pad( number_format( $summary['loc'] / max( 1, $summary['all'] ) * 100, 1 ), 10, ' ', STR_PAD_LEFT );
        
        foreach( $output as $line ) {
            
            echo $line . PHP_EOL;
            
        }
        
    }
    
    // start
    
    if( PHP_SAPI == 'cli' ) {
        
        echo '  --------------------------------------------------------' . PHP_EOL;
        echo '  LOC COUNTER build 0.0.1 alpha by komed3' . PHP_EOL;
        echo '  --------------------------------------------------------' . PHP_EOL . PHP_EOL;
        
        if( empty( $argv[1] ) || !is_dir( $argv[1] ) || !is_readable( $argv[1] ) )
            die( '  [ERROR] param 1 must be an readable directory or [?]' . PHP_EOL );
        
        if( !empty( $argv[2] ) && !in_array( $argv[2], [ 'files', 'all', 'loc', 'empty', 'pct' ] ) )
            die( '  [ERROR] param 2 must be one from list [ files, all, loc, empty ]' . PHP_EOL );
        
        loc_counter( $argv[1] );
        loc_output();
        
    } else die( '[ERROR] access denided' . PHP_EOL );
    
?>
