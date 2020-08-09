<?php

			 $labels = array(
				'name'             => _x( 'Tipos de Plato', 'taxonomy general name' ),
				'singular_name'    => _x( 'Tipo de Plato', 'taxonomy singular name' ),
				'search_items'     =>  __( 'Buscar por Tipo de Plato' ),
				'all_items'        => __( 'Todos los Tipos de Plato' ),
				'parent_item'      => __( 'Tipo de Plato padre' ),
				'parent_item_colon'=> __( 'Tipo de Plato padre:' ),
				'edit_item'        => __( 'Editar Tipo de Plato' ),
				'update_item'      => __( 'Actualizar Tipo de Plato' ),
				'add_new_item'     => __( 'Añadir nuevo Tipo de Plato' ),
				'new_item_name'    => __( 'Nombre del nuevo Tipo de Plato' ),
			  );
			  
			  /* Registramos la taxonomía y la configuramos como jerárquica (al estilo de las categorías) */
			  register_taxonomy( 'tipo_plato', array( 'plato' ), array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_ui'            => true,
				'query_var'          => true,
				/*'meta_box_cb'           => false,*/
			  ));
			  

			 $labels = array(
				'name'             => _x( 'Categorias de Plato', 'taxonomy general name' ),
				'singular_name'    => _x( 'Categoria de Plato', 'taxonomy singular name' ),
				'search_items'     =>  __( 'Buscar por Categoria de Plato' ),
				'all_items'        => __( 'Todos los Categorias de Plato' ),
				'parent_item'      => __( 'Categoria de Plato padre' ),
				'parent_item_colon'=> __( 'Categoria de Plato padre:' ),
				'edit_item'        => __( 'Editar Categoria de Plato' ),
				'update_item'      => __( 'Actualizar Categoria de Plato' ),
				'add_new_item'     => __( 'Añadir nuevo Categoria de Plato' ),
				'new_item_name'    => __( 'Nombre del nuevo Categoria de Plato' ),
			  );
			  
			  /* Registramos la taxonomía y la configuramos como jerárquica (al estilo de las categorías) */
			  register_taxonomy( 'categoria_plato', array( 'plato' ), array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_ui'            => true,
				'query_var'          => true,
				'meta_box_cb'           => false,
			  ));
  			

