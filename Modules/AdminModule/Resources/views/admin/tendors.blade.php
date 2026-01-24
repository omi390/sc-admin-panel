@extends('adminmodule::layouts.master')

@section('title',translate('profile_update'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">Tednors</h2>
                           <a href="{{ route('admin.tendors.create') }}" class="btn btn-primary btn-sm">Add New Tendor</a>
                    </div>

                   <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                 <th>Applicants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tendors as $tendor)
                            <tr>
                                <td>{{ $tendor->id }}</td>
                                <td>{{ $tendor->name }}</td>
                                <td>{{ $tendor->type }}</td>
                                <td>{{ $tendor->location }}</td>
                                  <td>  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customModal{{ $tendor->id }}">Applicants ({{count( $tendor->applicants)  }})
                                  
                                </button>
                                
                                <!-- Modal -->
                                <div class="modal fade" id="customModal{{ $tendor->id }}" tabindex="-1" aria-labelledby="customModalLabel" aria-hidden="true">
                                  <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                    
                                      <div class="modal-header">
                                        <h5 class="modal-title" id="customModalLabel">Applicants Info</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      
                                      
                                        <div class="modal-body">
                                                
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <td>Name</td>
                                                              <td>Mobile</td>
                                                                <td>Email</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($tendor->applicants as $applicant)
                                                        <tr>
                                                            <td>{{$applicant->name}}</td>
                                                            <td>{{$applicant->mobile}}</td>
                                                            <td>{{$applicant->address}}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            
                                        </div>
                                        
                                      
                               
                                
                                    </div>
                                  </div>
                                </div>
                                
                                </td>
                                <td>
                                    <a href="{{ route('admin.tendors.edit', $tendor->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <form action="{{ route('admin.tendors.destroy', $tendor->id) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

