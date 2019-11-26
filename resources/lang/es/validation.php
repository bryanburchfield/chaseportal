<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted'             => ':attribute debe ser aceptado.',
    'active_url'           => 'El valor de :attribute no es un URL válido.',
    'after'                => 'El valor de :attribute debe ser una fecha posterior a :date.',
    'after_or_equal'       => 'El valor de :attribute debe ser una fecha posterior o igual a :date.',
    'alpha'                => ':attribute debe contener sólo letras.',
    'alpha_dash'           => ':attribute debe contener sólo letras, números y guiones.',
    'alpha_num'            => ':attribute debe contener sólo letras y números.',
    'array'                => ':attribute debe ser un vector de datos.',
    'before'               => 'El valor de :attribute debe ser una fecha anterior a :date.',
    'before_or_equal'      => 'El valor de :attribute debe ser una fecha anterior ó igual a :date.',
    'between'              => [
        'numeric' => 'El valor de :attribute debe ser entre :min y :max.',
        'file'    => 'El tamaño de :attribute debe ser entre :min y :max kilobytes.',
        'string'  => ':attribute debe tener entre :min y :max caracteres.',
        'array'   => ':attribute debe tener entre :min y :max elementos.',
    ],
    'boolean'              => 'El valor de :attribute debe ser Verdadero o Falso.',
    'confirmed'            => 'La confirmación de :attribute no coincide.',
    'date'                 => 'El valor de :attribute no es una fecha válida.',
    'date_equals'          => 'El valor de :attribute debe ser una fecha igual a :date.',
    'date_format'          => 'El valor de :attribute no corresponde al formato :format.',
    'different'            => 'Los valores de :attribute y :other deben ser diferentes.',
    'digits'               => ':attribute debe tener :digits dígitos.',
    'digits_between'       => ':attribute debe tener entre :min y :max dígitos.',
    'dimensions'           => ':attribute presenta dimensiones de imagen inválidas.',
    'distinct'             => 'El campo :attribute contiene un valor duplicado.',
    'email'                => 'El valor de :attribute debe ser una dirección de email válida',
    'ends_with'            => 'El campo :attribute debe finalizar con uno de los siguientes valores: :values',
    'exists'               => 'El :attribute selecionado es inválido.',
    'file'                 => ':attribute debe ser un archivo.',
    'filled'               => 'El campo :attribute debe tener un valor.',
    'gt'                   => [
        'numeric' => 'El valor de :attribute debe ser mayor que :value.',
        'file'    => 'El tamaño de :attribute debe se mayor que :value kilobytes.',
        'string'  => ':attribute debe tener más de :value caracteres.',
        'array'   => ':attribute debe tener más de :value elementos.',
    ],
    'gte'                  => [
        'numeric' => 'El valor de :attribute debe ser mayor que o igual a :value.',
        'file'    => 'El tamaño de :attribute debe ser mayor que o igual a :value kilobytes.',
        'string'  => ':attribute debe tener más que o igual que :value caracteres.',
        'array'   => ':attribute debe tener más que o igual que :value elementos.',
    ],
    'image'                => ':attribute debe ser una imagen.',
    'in'                   => 'El valor de :attribute es inválido.',
    'in_array'             => ':attribute no existe en :other.',
    'integer'              => 'El valor de :attribute debe ser un entero.',
    'ip'                   => ':attribute debe ser una dirección IP válida.',
    'ipv4'                 => ':attribute debe ser un dirección IPv4 válida',
    'ipv6'                 => ':attribute debe ser un dirección IPv6 válida.',
    'json'                 => 'El valor de :attribute debe ser una cadena de caracteres JSON válida.',
    'lt'                   => [
        'numeric' => 'El valor de :attribute debe ser menor o igual que :value.',
        'file'    => 'El tamaño de :attribute debe ser menor que :value kilobytes.',
        'string'  => ':attribute debe tener menos de :value caracteres.',
        'array'   => ':attribute debe tener menos de :value elementos.',
    ],
    'lte'                  => [
        'numeric' => 'El valor de :attribute debe ser menor que o igual a :value.',
        'file'    => 'El tamaño de :attribute debe ser menor que o igual a :value kilobytes.',
        'string'  => ':attribute debe tener menos que o igual que :value caracteres.',
        'array'   => ':attribute debe tener menos que o igual que :value elementos.',
    ],
    'max'                  => [
        'numeric' => 'El valor de :attribute debe ser como máximo :max.',
        'file'    => 'El tamaño de :attribute debe ser como máximo :max kilobytes.',
        'string'  => ':attribute debe tener como máximo :max caracteres.',
        'array'   => ':attribute debe tener como máximo :max elementos.',
    ],
    'mimes'                => ':attribute debe ser un archivo con formato: :values.',
    'mimetypes'            => ':attribute debe ser un archivo con formato: :values.',
    'min'                  => [
        'numeric' => 'El valor de :attribute debe ser como mínimo :min.',
        'file'    => 'El tamaño de :attribute debe ser como mínimo :min kilobytes.',
        'string'  => ':attribute debe tener como mínimo :min caracteres.',
        'array'   => ':attribute debe tener como mínimo :min elementos.',
    ],
    'not_in'               => 'El :attribute seleccionado es inválido.',
    'not_regex'            => 'El formato de :attribute no es válido.',
    'numeric'              => 'El valor de :attribute debe ser numérico.',
    'password'             => 'La contraseña es incorrecta.',
    'present'              => 'El campo :attribute debe estar presente.',
    'regex'                => 'El formato de :attribute es inválido.',
    'required'             => 'El campo :attribute es requerido.',
    'required_if'          => 'El campo :attribute es requerido cuando :other es :value.',
    'required_unless'      => 'El campo :attribute es requerido a no ser que :other esté en :values.',
    'required_with'        => 'El campo :attribute es requerido cuando :values está presente.',
    'required_with_all'    => 'El campo :attribute es requerido cuando :values están presentes.',
    'required_without'     => 'El campo :attribute es requerido cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es requerido cuando ninguno de :values están presentes.',
    'same'                 => ':attribute y :other deben coincidir.',
    'size'                 => [
        'numeric' => 'El valor de :attribute debe ser :size.',
        'file'    => 'El tamaño de :attribute debe ser :size kilobytes.',
        'string'  => ':attribute debe tener :size caracteres.',
        'array'   => ':attribute debe contener :size elementos.',
    ],
    'starts_with'          => ':attribute debe comenzar con uno de los siguientes: :values',
    'string'               => ':attribute debe ser una cadena de caracteres.',
    'timezone'             => ':attribute debe ser una zona válida.',
    'unique'               => ':attribute ya ha sido usado.',
    'uploaded'             => ':attribute falló en subir.',
    'url'                  => 'El formato de :attribute es inválido.',
    'uuid'                 => ':attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'password' => [
            'min' => 'La :attribute debe ser más de :min caracteres',
        ],
        'email'    => [
            'unique' => 'El :attribute ya ha sido registrado.',
        ],
        'filter_rule' => [
            'same_source_destination' => 'La campaña/sub-campaña de origen y destino deben ser diferentes.',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'address'               => 'dirección',
        'age'                   => 'edad',
        'body'                  => 'cuerpo',
        'city'                  => 'ciudad',
        'content'               => 'contenido',
        'country'               => 'país',
        'date'                  => 'fecha',
        'day'                   => 'día',
        'description'           => 'descripción',
        'email'                 => 'correo electrónico',
        'excerpt'               => 'extracto',
        'first_name'            => 'nombre',
        'gender'                => 'género',
        'hour'                  => 'hora',
        'last_name'             => 'apellido',
        'message'               => 'mensaje',
        'minute'                => 'minuto',
        'mobile'                => 'móvil',
        'month'                 => 'mes',
        'name'                  => 'nombre',
        'password'              => 'contraseña',
        'password_confirmation' => 'confirmación de la contraseña',
        'phone'                 => 'teléfono',
        'price'                 => 'precio',
        'second'                => 'segundo',
        'sex'                   => 'sexo',
        'subject'               => 'asunto',
        'terms'                 => 'términos',
        'time'                  => 'hora',
        'title'                 => 'título',
        'username'              => 'usuario',
        'year'                  => 'año',
    ],
];
